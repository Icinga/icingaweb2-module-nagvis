# Installation

## Requirements

This module glues NagVis and Icinga Web 2 together. Both of them are required
to be installed and configured:

* [Icinga Web 2](https://www.icinga.com/products/icinga-web-2/) (>= 2.4.1)
* [NagVis](https://www.nagvis.org/) (&gt;= 1.8)


## Install module

Extract this module to your Icinga Web 2 modules directory as `nagvis` directory.

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
everything working as expected, your NagVis configuration should contain one of
the following settings inside `nagvis.ini.php`

### Icinga DB

```ini
[global]
authmodule="CoreAuthModIcingaweb2"
authorisationmodule="CoreAuthorisationModIcingaweb2"
logonmodule="LogonIcingaweb2"

[paths]
htmlcgi = "/icingaweb2"

[defaults]
backend = "icingadb"

urltarget = "_top"
hosturl="[htmlcgi]/icingadb/host?name=[host_name]"
hostgroupurl="[htmlcgi]/icingadb/hostgroup?name=[hostgroup_name]"
serviceurl="[htmlcgi]/icingadb/service?host.name=[host_name]&name=[service_description]"
servicegroupurl="[htmlcgi]/icingadb/servicegroup?name=[servicegroup_name]"
mapurl="[htmlcgi]/nagvis/show/map?map=[map_name]"
stylesheet="icingaweb-nagvis-integration.css"

[backend_icingadb]
backendtype="icingadb"
dbhost="localhost"
dbport=3306
dbname="icingadb"
dbuser="icingadb"
dbpass="icingadb"
dbinstancename="default"
;maxtimewithoutupdate=180
```

### Monitoring (IDO)

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
`<nagvisdir>/share/server/core/functions/core.php`:

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

Additionally, to make sure that the `icingadb` backend type provided by this module is being detected by `Nagvis`, you
either need to manually copy the `/usr/share/icingaweb2/modules/nagvis/library/nagvis-includes/GlobalBackendicingadb.php`
file into the `<nagvisdir>/nagvis/share/server/core/classes` directory or you can just create a symlink.

```bash
ln -s /usr/share/icingaweb2/modules/nagvis/library/nagvis-includes/GlobalBackendicingadb.php <nagvisdir>/nagvis/share/server/core/classes/GlobalBackendicingadb.php
```

### PHP on CentOS

**Warning:** If you are running NagVis on CentOS - you also have to switch NagVis to PHP-FPM, so that the runtime
environment has access to the same PHP session data (if not you only see "not authenticated").

The following options are possible:

**Switch all PHP inside Apache to FPM**

Note: Only works with Apache >= 2.4

Add `/etc/httpd/conf.d/php-fpm.conf`:
```apache
DirectoryIndex index.php
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

<FilesMatch "\.php$">
    SetHandler "proxy:fcgi://127.0.0.1:9000"
    ErrorDocument 503 /icingaweb2/error_unavailable.html
</FilesMatch>
```

**Switch NagVis to PHP-FPM**

Please make sure to use at least NagVis 1.9.5!

Update `/etc/httpd/conf.d/nagvis.conf` similar to [icingaweb2.conf](https://github.com/Icinga/icingaweb2/blob/master/packages/files/apache/icingaweb2.fpm.conf).

**Adjust the session.save_path**

For system PHP running in Apache httpd, update `/etc/httpd/conf.d/php.conf`:
```apache
php_value session.save_path    "/var/opt/rh/rh-php71/lib/php/session"
```

**Adjust the include_path**

Update `/etc/opt/rh/rh-php71/php.ini`:
```apache
include_path = ".:/usr/share/php:/opt/rh/rh-php71/root/usr/share/pear:/opt/rh/rh-php71/root/usr/share/php"
```

Also see [PHP-FPM in Apache httpd Wiki](https://wiki.apache.org/httpd/PHP-FPM).
