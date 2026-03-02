<?php

namespace Piwik\Plugins\PageviewUrlLookup;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu): void
    {
        if (!Piwik::isUserHasSomeAdminAccess()) {
            return;
        }

        $menu->addSystemItem(
            Piwik::translate('PageviewUrlLookup_MenuTitle'),
            $this->urlForAction('index'),
            $order = 36
        );
    }
}
