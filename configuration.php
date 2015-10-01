<?php

use Icinga\Application\Config;

$section = $this->menuSection($this->translate('Maps'))
    ->setUrl('nagvis/show/map')
    ->setIcon('globe');

foreach (Config::module('nagvis')->getSection('menu') as $name => $caption) {
    $section->add($caption)->setUrl('nagvis/show/map', array('map' => $name));
}

$this->providePermission(
    'nagvis/read',
    $this->translate('Show NagVis maps')
);

$this->providePermission(
    'nagvis/edit',
    $this->translate('Modify NagVis maps')
);

$this->providePermission(
    'nagvis/admin',
    $this->translate('Nagvis administration')
);

$this->provideRestriction(
    'nagvis/map/filter',
    $this->translate('Filter NagVis maps')
);
