<?php

namespace Piwik\Plugins\PageviewUrlLookup;

use Piwik\Common;
use Piwik\Date;
use Piwik\Log;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;

class Controller extends \Piwik\Plugin\ControllerAdmin
{
    public const NONCE_NAME = 'PageviewUrlLookup.search';

    public function index(): string
    {
        Piwik::checkUserHasSomeAdminAccess();

        $sitesWithAdminAccess = SitesManagerAPI::getInstance()->getSitesWithAdminAccess();
        if (empty($sitesWithAdminAccess)) {
            throw new \Exception('No site available with admin access.');
        }

        $defaultSiteId = (int) ($this->idSite ?: $sitesWithAdminAccess[0]['idsite']);
        $idSite = Common::getRequestVar('idSite', $defaultSiteId, 'int');
        Piwik::checkUserHasAdminAccess($idSite);

        $defaultLookupEndDate = Date::factory('today')->toString('Y-m-d');
        $defaultLookupStartDate = Date::factory('today')->subDay(29)->toString('Y-m-d');
        $lookupStartDate = Common::getRequestVar('lookupStartDate', $defaultLookupStartDate, 'string');
        $lookupEndDate = Common::getRequestVar('lookupEndDate', $defaultLookupEndDate, 'string');
        $urlQuery = Common::getRequestVar('urlQuery', '', 'string');
        $matchType = Common::getRequestVar('matchType', Service::MATCH_EXACT, 'string');

        $result = null;
        $errorMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nonce = Common::getRequestVar('nonce', '', 'string');
                Nonce::checkNonce(self::NONCE_NAME, $nonce);

                $result = (new Service())->searchAndStore(
                    $idSite,
                    $lookupStartDate,
                    $lookupEndDate,
                    $urlQuery,
                    $matchType,
                    Piwik::getCurrentUserLogin()
                );
            } catch (\InvalidArgumentException $e) {
                $errorMessage = Piwik::translate($e->getMessage());
            } catch (\Throwable $e) {
                Log::warning('PageviewUrlLookup unexpected error: ' . $e->getMessage());
                $errorMessage = Piwik::translate('PageviewUrlLookup_ErrorGeneric');
            }
        }

        $savedRows = (new Model())->getRecentRowsForSite($idSite, 300);

        return $this->renderTemplate('index', [
            'idSite' => $idSite,
            'lookupStartDate' => $lookupStartDate,
            'lookupEndDate' => $lookupEndDate,
            'urlQuery' => $urlQuery,
            'matchType' => $matchType,
            'result' => $result,
            'errorMessage' => $errorMessage,
            'savedRows' => $savedRows,
            'sitesWithAdminAccess' => $sitesWithAdminAccess,
            'nonce' => Nonce::getNonce(self::NONCE_NAME),
        ]);
    }
}
