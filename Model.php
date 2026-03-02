<?php

namespace Piwik\Plugins\PageviewUrlLookup;

use Piwik\Common;
use Piwik\Db;

class Model
{
    public const TABLE_NAME = 'pageview_url_lookup_report';
    private static $tableEnsured = false;

    public function addReportRow(array $row): int
    {
        self::ensureTableExists();

        $table = Common::prefixTable(self::TABLE_NAME);

        Db::query(
            "INSERT INTO `{$table}` (
                `idsite`,
                `period`,
                `date`,
                `date_start`,
                `date_end`,
                `timezone`,
                `url_query`,
                `match_type`,
                `normalized_url_query`,
                `pageviews`,
                `created_by_login`,
                `created_at`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $row['idsite'],
                $row['period'],
                $row['date'],
                $row['date_start'],
                $row['date_end'],
                $row['timezone'],
                $row['url_query'],
                $row['match_type'],
                $row['normalized_url_query'],
                $row['pageviews'],
                $row['created_by_login'],
                $row['created_at'],
            ]
        );

        return (int) Db::get()->lastInsertId();
    }

    public function getRecentRowsForSite(int $idSite, int $limit = 200): array
    {
        self::ensureTableExists();

        $table = Common::prefixTable(self::TABLE_NAME);
        $limit = max(1, min($limit, 1000));

        $sql = "SELECT *
                FROM `{$table}`
                WHERE `idsite` = ?
                ORDER BY `id` DESC
                LIMIT {$limit}";

        $rows = Db::fetchAll($sql, [$idSite]);

        return $rows ?: [];
    }

    public static function ensureTableExists(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        $table = Common::prefixTable(self::TABLE_NAME);

        Db::exec("CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `idsite` INT(10) UNSIGNED NOT NULL,
            `period` VARCHAR(20) NOT NULL,
            `date` VARCHAR(60) NOT NULL,
            `date_start` DATE NOT NULL,
            `date_end` DATE NOT NULL,
            `timezone` VARCHAR(64) NOT NULL,
            `url_query` TEXT NOT NULL,
            `match_type` VARCHAR(20) NOT NULL,
            `normalized_url_query` TEXT NOT NULL,
            `pageviews` INT(10) UNSIGNED NOT NULL,
            `created_by_login` VARCHAR(100) NOT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `index_site_created_at` (`idsite`, `created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        self::$tableEnsured = true;
    }
}
