# NagVis - Configuration
This module provides everything for a complete NagVis integration into Icinga Web 2 also including authentication and authorisation.


## NagVis config file nagvis.ini.php
To get everything working as expected, your NagVis configuration `<nagvis-conf-dir>/nagvis.ini.php` should contain
the following settings:

```ini
[global]
authmodule="CoreAuthModIcingaweb2"
authorisationmodule="CoreAuthorisationModIcingaweb2"
logonmodule="LogonIcingaweb2"

[paths]
htmlcgi = "/icingaweb2"

[defaults]
backend = "ndomy_1"
urltarget = "_top"
hosturl = "[htmlcgi]/monitoring/show/host?host=[host_name]"
hostgroupurl = "[htmlcgi]/monitoring/show/hostgroup?hostgroup=[hostgroup_name]"
serviceurl = "[htmlcgi]/monitoring/show/service?host=[host_name]&service=[service_description]"
servicegroupurl = "[htmlcgi]/monitoring/show/servicegroup?servicegroup=[servicegroup_name]"
mapurl="[htmlcgi]/nagvis/show/map?map=[map_name]"
headermenu="0"
stylesheet="icingaweb-nagvis-integration.css"
```

## Icingaweb stylesheet

The CSS file `<icingweb2-module-nagvis>/public/css/icingaweb-nagvis-integration.css` must be copied to
`<nagvis-dir>/share/userfiles/styles`.



## Icingweb2 PHP code integration into NagVis

To get the integration running and to allow NagVis to find the configured
handlers you need to add a short piece of code to

`<nagvis-dir>/share/server/core/functions/index.php`:

```php
/**
 * Icinga Web 2 integration 
 */
use Icinga\Application\EmbeddedWeb;

require_once 'Icinga/Application/EmbeddedWeb.php';
require_once EmbeddedWeb::start(null, '/etc/icingaweb2')
    ->getModuleManager()
    ->getModule('nagvis')
    ->getLibDir() . '/nagvis-includes/init.inc.php';
```

This has to sit on top of the page, but after the `<?php` line.

