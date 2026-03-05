<?php

namespace Piwik\Plugins\PageviewUrlLookup;

use Piwik\Date;
use Piwik\Db;
use Piwik\Site;
use Piwik\Tracker\Action;

class Service
{
    public const MATCH_EXACT = 'exact';
    public const MATCH_CONTAINS = 'contains';

    /**
     * @return array<string,mixed>
     */
    public function searchAndStore(
        int $idSite,
        string $startDate,
        string $endDate,
        string $urlQuery,
        string $matchType,
        string $createdByLogin
    ): array {
        $urlQuery = trim($urlQuery);
        if ($urlQuery === '') {
            throw new \InvalidArgumentException('PageviewUrlLookup_ErrorUrlRequired');
        }
        if (strlen($urlQuery) > 2048) {
            throw new \InvalidArgumentException('PageviewUrlLookup_ErrorUrlTooLong');
        }

        if (!in_array($matchType, [self::MATCH_EXACT, self::MATCH_CONTAINS], true)) {
            throw new \InvalidArgumentException('PageviewUrlLookup_ErrorInvalidMatchType');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            throw new \InvalidArgumentException('PageviewUrlLookup_ErrorInvalidDate');
        }
        if (!checkdate((int)substr($startDate, 5, 2), (int)substr($startDate, 8, 2), (int)substr($startDate, 0, 4))
            || !checkdate((int)substr($endDate, 5, 2), (int)substr($endDate, 8, 2), (int)substr($endDate, 0, 4))) {
            throw new \InvalidArgumentException('PageviewUrlLookup_ErrorInvalidDate');
        }

        if ($startDate > $endDate) {
            throw new \InvalidArgumentException('PageviewUrlLookup_ErrorInvalidDateRange');
        }

        if ((strtotime($endDate) - strtotime($startDate)) / 86400 > 366) {
            throw new \InvalidArgumentException('PageviewUrlLookup_ErrorDateRangeTooLarge');
        }

        $timezone = Site::getTimezoneFor($idSite);
        $dateStart = $startDate;
        $dateEnd = $endDate;
        $dateTimeStart = Date::factory($dateStart . ' 00:00:00', $timezone)->getDatetime();
        $dateTimeEnd = Date::factory($dateEnd . ' 23:59:59', $timezone)->getDatetime();

        $normalizedUrlQuery = $this->normalizeUrlQuery($urlQuery, $matchType);
        $pageviews = $this->countPageviews($idSite, $dateTimeStart, $dateTimeEnd, $normalizedUrlQuery, $matchType);

        $row = [
            'idsite' => $idSite,
            'period' => 'range',
            'date' => $dateStart . ',' . $dateEnd,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'timezone' => $timezone,
            'url_query' => $urlQuery,
            'match_type' => $matchType,
            'normalized_url_query' => $normalizedUrlQuery,
            'pageviews' => $pageviews,
            'created_by_login' => $createdByLogin,
            'created_at' => Date::now()->getDatetime(),
        ];

        $idRow = (new Model())->addReportRow($row);
        $row['id'] = $idRow;

        return $row;
    }

    private function countPageviews(
        int $idSite,
        string $dateTimeStart,
        string $dateTimeEnd,
        string $urlQuery,
        string $matchType
    ): int {
        $sql = "SELECT COUNT(*) AS pageviews
                FROM `" . \Piwik\Common::prefixTable('log_link_visit_action') . "` lva
                INNER JOIN `" . \Piwik\Common::prefixTable('log_visit') . "` lv
                    ON lv.idvisit = lva.idvisit
                INNER JOIN `" . \Piwik\Common::prefixTable('log_action') . "` la
                    ON la.idaction = lva.idaction_url
                WHERE lv.idsite = ?
                  AND lva.server_time >= ?
                  AND lva.server_time <= ?
                  AND la.type = ?";

        $bind = [$idSite, $dateTimeStart, $dateTimeEnd, Action::TYPE_PAGE_URL];

        if ($matchType === self::MATCH_CONTAINS) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $urlQuery);
            $sql .= " AND la.name LIKE CONCAT('%', ?, '%')";
            $bind[] = $escaped;
        } else {
            $sql .= " AND la.name = ?";
            $bind[] = $urlQuery;
        }

        return (int) Db::fetchOne($sql, $bind);
    }

    private function normalizeUrlQuery(string $urlQuery, string $matchType): string
    {
        if ($matchType === self::MATCH_CONTAINS) {
            return $urlQuery;
        }

        return (string) preg_replace('@^https?://(www\.)?@i', '', $urlQuery);
    }
}
