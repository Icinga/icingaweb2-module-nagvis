# NagVis module for Icinga Web 2

#### Table of Contents

1. [About](#about)
2. [License](#license)
3. [Support](#support)
4. [Requirements](#requirements)
5. [Installation](#installation)
6. [Configuration](#configuration)
7. [FAQ](#faq)
8. [Thanks](#thanks)
9. [Contributing](#contributing)

## About

This module integrates [NagVis](https://www.nagvis.org/) into [Icinga Web 2](https://www.icinga.com/products/icinga-web-2/)
and allows you to create beautiful maps for your datacenter, geolocation based maps or dynamic maps with parent-child
relations.

<img src="https://github.com/Icinga/icingaweb2-module-nagvis/blob/master/doc/screenshot/geomap.png" alt="Geomap" height="300">

<img src="https://github.com/Icinga/icingaweb2-module-nagvis/blob/master/doc/screenshot/groups.png" alt="Groups" height="300">

## License

Icinga Web 2 and this Icinga Web 2 module are licensed under the terms of the GNU General Public License Version 2, you will find a copy of this license in the LICENSE file included in the source package.

## Support

Join the [Icinga community channels](https://www.icinga.com/community/get-involved/) for questions.

## Requirements

This module glues NagVis and Icinga Web 2 together. Both of them are required
to be installed and configured:

* [Icinga Web 2](https://www.icinga.com/products/icinga-web-2/) (>= 2.4.1)
* [NagVis](https://www.nagvis.org/) (&gt;= 1.8)


## Installation

Extract this module to your Icinga Web 2 modules directory as `map` directory.

Git clone:

```
cd /usr/share/icingaweb2/modules
git clone https://github.com/Icinga/icingaweb2-module-nagvis.git nagvis
```


Tarball download (latest [release](https://github.com/Icinga/icingaweb2-module-nagvis/releases/latest)):

```
cd /usr/share/icingaweb2/modules
wget https://github.com/Icinga/icingaweb2-module-nagvis/archive/v1.1.1.zip
unzip v1.1.1.zip
mv icingaweb2-module-nagvis-1.1.1 nagvis
```

### Enable Icinga Web 2 module

Enable the module in the Icinga Web 2 frontend in `Configuration -> Modules -> nagvis -> enable`.
You can also enable the module by using the `icingacli` command:

```
icingacli module enable nagvis
```

## Configuration

### NagVis Configuration

This module provides everything for a complete Icinga Web 2 NagVis
integration, including authentication and authorisation. To get
everything working as expected, your NagVis configuration should contain
the following settings inside `nagvis.ini.php`

```ini
[global]
authmodule="CoreAuthModIcingaweb2"
authorisationmodule="CoreAuthorisationModIcingaweb2"
logonmodule="LogonIcingaweb2"

[paths]
htmlcgi = "/icingaweb2"

[defaults]
; This selects the backend_ndomy_1 defined below
backend = "ndomy_1"

urltarget = "_top"
hosturl="[htmlcgi]/monitoring/host/show?host=[host_name]"
hostgroupurl="[htmlcgi]/monitoring/list/hostgroups?hostgroup_name=[hostgroup_name]"
serviceurl="[htmlcgi]/monitoring/service/show?host=[host_name]&service=[service_description]"
servicegroupurl="[htmlcgi]/monitoring/list/servicegroups?servicegroup_name=[servicegroup_name]"
mapurl="[htmlcgi]/nagvis/show/map?map=[map_name]"
headermenu="0"
stylesheet="icingaweb-nagvis-integration.css"

[backend_ndomy_1]
backendtype="ndomy"
dbhost="localhost"
dbport=3306
dbname="icinga"
dbuser="icinga"
dbpass="icinga"
dbprefix="icinga_"
dbinstancename="default"
;maxtimewithoutupdate=180
```

You can specify an alternative backend, i.e. `livestatus` or `pgsql` (since 1.9.x).
Please check the NagVis documentation for details.

#### CSS Integration

The CSS file `public/css/icingaweb-nagvis-integration.css` must be copied to
`<nagvisdir>/share/userfiles/styles`.

### Module Configuration

For many environments the module needs no special configuration. Usually
you might want to add a bunch of main maps directly to your menu - this
can be done in `<ICINGAWEB_CONFIGDIR>/modules/nagvis/config.ini` like in
the following example:

```ini
[global]
default-map = demo-overview

[menu]
demo-germany = Germany
demo-ham-racks = Hamburg
```

### Icinga Web 2 Configuration

Since Icinga Web 2.2.0 session cookies are restricted to `/icingaweb2` or
your Icinga Web 2 base path. This means that your browser would not send
your session cookie to `/nagvis` and you would not be granted access to
NagVis. To solve this please add a section as follows to your Icinga Web 2
configuration in `/etc/icingaweb2/config.ini`:

```ini
[cookie]
path = /
```

Before doing so please log out from Icinga Web 2 and close your browser,
just to be on the safe side. You could otherwise lock your browser in a
redirection loop.

### PHP code integration

To get the integration running and to allow NagVis to find the configured
handlers you need to add a short piece of code to
`<nagvisdir>/share/server/core/functions/index.php`:

```php
/**
 * Icinga Web 2 integration
 */
use Icinga\Application\EmbeddedWeb;

require_once 'Icinga/Application/EmbeddedWeb.php';
require_once EmbeddedWeb::start('/usr/share/icingaweb2', '/etc/icingaweb2')
    ->getModuleManager()
    ->getModule('nagvis')
    ->getLibDir() . '/nagvis-includes/init.inc.php';
```

This has to sit on top of the page, but after the `<?php` line.


## FAQ

### URLs to Icinga Web 2 views do not work

Ensure that `urltarget = "_top"` is set inside the `default` section
in the `nagvis.ini.php` configuration file.

### Map Path demo-overview.cfg doesn't exist

Specify a different `default-map` in the `nagvis.ini.php` configuration file.

## Thanks



## Contributing

There are many ways to contribute to the Icinga Web module for NagVis --
whether it be sending patches, testing, reporting bugs, or reviewing and
updating the documentation. Every contribution is appreciated!


