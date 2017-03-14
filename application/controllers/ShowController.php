<?php

namespace Icinga\Module\Nagvis\Controllers;

use Icinga\Module\Nagvis\RestrictionHelper;
use Icinga\Security\SecurityException;
use Icinga\Web\Controller;
use Icinga\Web\Url;
use Icinga\Web\Widget\Tabextension\DashboardAction;
use Icinga\Web\Widget\Tabextension\MenuAction;

class ShowController extends Controller
{
    public function getTabs()
    {
        $tabs = parent::getTabs ();
        $tabs->add (
            'index',
            array (
                'title' => 'Nagvis',
                'url' => Url::fromRequest ()->getRelativeUrl ()
            )
        )->extend(
            new DashboardAction()
        )->extend(
            new MenuAction()
        );

        $menu_param = $this->params->get('showMenu');
        if (isset($menu_param) && $menu_param == 1) {
            $menu_text = "Hide NagVis Menu";
            $menu_url = $this->getRequest()->getUrl()->without('showMenu');
            $icon = 'eye-off';
        } else {
            $menu_text = "Show NagVis Menu";
            $menu_url = $this->getRequest()->getUrl()->with('showMenu', 1);
            $icon = 'eye';
        }

        $tabs->addAsDropdown(
            'nagvis-menu-entry',
            array(
                'icon'  => $icon,
                'label' => t($menu_text),
                'url'   => $menu_url
            )
        );

        return $tabs;
    }

    public function mapAction()
    {
        // TODO: I'd prefer to have mod=Overview as a default, that would also
        //       work with no enabled map. Unfortunately Overview doesn't seem
        //       to respect header_menu=0
        $map = $this->params->get(
            'map',
            $this->Config()->get(
                'global',
                'default-map',
                'demo-overview'
            )
        );

        $restriction = RestrictionHelper::getRegex();
        if ($restriction !== null && ! preg_match($restriction, $map)) {
            throw new SecurityException('You\'re not allowed to view map "%s"', $map);
        }

        $baseurl = $this->Config()->get('global', 'baseurl', '/nagvis');

        $url = $baseurl . '/frontend/nagvis-js/index.php';
        $url .= '?mod=Map&act=view&show=' . urlencode($map);

        if ($this->params->get('showMenu')) {
            $url .= '&header_menu=1';
        } else {
            $url .= '&header_menu=0';
        }

        $zoom = $this->params->shift('zoom', $this->view->compact ? 47 : null);
        if ($zoom) {
            $url .= '&zoom=' . (int) $zoom;
        }

        if ($height = $this->params->shift('height')) {
            $this->view->height = (int) $height;
        }

        $this->view->nagvisUrl = $url;
	    $this->getTabs ()->activate('index');
    }
}
