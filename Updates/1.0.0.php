<?php

namespace Piwik\Plugins\PageviewUrlLookup;

use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_1_0_0 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater): array
    {
        return [
            $this->migration->db->createTable(
                Model::TABLE_NAME,
                [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    'idsite' => 'INT(10) UNSIGNED NOT NULL',
                    'period' => 'VARCHAR(20) NOT NULL',
                    'date' => 'VARCHAR(60) NOT NULL',
                    'date_start' => 'DATE NOT NULL',
                    'date_end' => 'DATE NOT NULL',
                    'timezone' => 'VARCHAR(64) NOT NULL',
                    'url_query' => 'TEXT NOT NULL',
                    'match_type' => 'VARCHAR(20) NOT NULL',
                    'normalized_url_query' => 'TEXT NOT NULL',
                    'pageviews' => 'INT(10) UNSIGNED NOT NULL',
                    'created_by_login' => 'VARCHAR(100) NOT NULL',
                    'created_at' => 'DATETIME NOT NULL',
                ],
                'id'
            ),
            $this->migration->db->addIndex(Model::TABLE_NAME, ['idsite', 'created_at'], 'index_site_created_at'),
        ];
    }

    public function doUpdate(Updater $updater): void
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
