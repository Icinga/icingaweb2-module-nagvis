<?php

// namespace Icinga\Module\Nagvis\Controllers;

use Icinga\Web\Controller;

class Nagvis_ShowController extends Controller
{
    public function mapAction()
    {
        // TODO: I'd prefer to have mod=Overview as a default, that would also
        //       work with no enabled map. Unfortunately Overview doesn't seem
        //       to respect header_menu=0
        $map = $this->params->get('map', $this->Config()->get('global', 'default-map', 'demo-overview'));
        $baseurl = $this->Config()->get('global', 'baseurl', '/nagvis');

        $url = $baseurl . '/frontend/nagvis-js/index.php';
        $url .= '?mod=Map&act=view&show=' . urlencode($map);

        if ($this->params->get('showMenu')) {
            $this->view->toggleMenuUrl = $this->getRequest()->getUrl()->without('showMenu');
            $this->view->toggleMenuCaption = $this->translate('Hide menu');
            $url .= '&header_menu=1';
        } else {
            $this->view->toggleMenuUrl = $this->getRequest()->getUrl()->with('showMenu', true);
            $this->view->toggleMenuCaption = $this->translate('Show menu');
            $url .= '&header_menu=0';
        }
        $this->view->nagvisUrl = $url;
    }
}
