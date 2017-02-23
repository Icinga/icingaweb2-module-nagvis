<?php

use Icinga\Application\Config;

$section = $this->menuSection(N_('Maps'))
    ->setUrl('nagvis/show/map')
    ->setIcon('globe');

$prio = 0;
foreach (Config::module('nagvis')->getSection('menu') as $name => $caption) {
    $section->add($caption, array(
        'url'           => 'nagvis/show/map',
        'urlParameters' => array('map' => $name),
        'priority'      => ++$prio
    ));
}

$this->providePermission(
    'nagvis/edit',
    $this->translate('Modify NagVis maps')
);

$this->providePermission(
    'nagvis/admin',
    $this->translate('Nagvis administration')
);

$this->providePermission(
    'nagvis/overview',
    $this->translate('NagVis general overview')
);

$this->provideRestriction(
    'nagvis/map/filter',
    $this->translate('Filter NagVis maps')
);
