<?php

namespace Piwik\Plugins\PageviewUrlLookup;

class PageviewUrlLookup extends \Piwik\Plugin
{
    public function install(): void
    {
        Model::ensureTableExists();
    }

    public function activate(): void
    {
        Model::ensureTableExists();
    }
}
