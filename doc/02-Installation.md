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

### PHP on CentOS

**Warning:** If you are running NagVis on CentOS you must account for two additional factors:

1. graphviz will need to be installed
2. icingaweb2 may use an PHP runtime that is not the vendor PHP
3. icingaweb2 runs under PHP-FPM
4. SELinux

The following instructions have been tested on CentOS 7.x.

#### Graphviz installation

Graphviz, required by `nagviz` may not be installed. Ensure it is installed before beginning the installation:

```shell
yum install -q -y grapviz
```

#### PHP runtime update
The `icingaweb2` module uses the PHP 7.1 runtime from the Red Hat Sofware Collections. The `php` program is not in the system path and the nagvis `install.sh` script will not find the PHP program it uses. Before running `install.sh`, issue this command to alter the path to include the PHP 7.1 `php` program:

```shell
export PATH=/opt/rh/rh-php71/root/bin:"$PATH"
```

#### PHP-FPM on CentOS

The nagvis install.sh script does not account for PHP-FPM, so you must make some manual changes to compensate.

Switch NagVis to run under PHP-FPM, so that the runtime environment has access to the same PHP session data. If you do not do this, you will only see the message "not authenticated" in the browser.

See [PHP-FPM in Apache httpd Wiki](https://wiki.apache.org/httpd/PHP-FPM) for more information about PHP-FPM.

The following options are possible:

##### Switch _all_ PHP inside Apache to FPM

*Note*: This only works with Apache >= 2.4

This configuration switches _all_ the PHP scripts on the server to run via PHP-FPM. If you have no other PHP applications running on the server except NagVis and icingaweb2, this is an easy option. However, if other scripts run via `mod_php` this may disrupt their functioning.

Add `/etc/httpd/conf.d/php-fpm.conf`:
```apache
DirectoryIndex index.php
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

<FilesMatch "\.php$">
    SetHandler "proxy:fcgi://127.0.0.1:9000"
    ErrorDocument 503 /icingaweb2/error_unavailable.html
</FilesMatch>
```

##### Switch NagVis to PHP-FPM

This configuration switches _only_ NagVis to run under PHP-FPM. This is a conservative option that will not disrupt the operation of other PHP applications on the server.

Please make sure to use at least NagVis 1.9.5!

Update `/etc/httpd/conf.d/nagvis.conf` similar to [icingaweb2.conf](https://github.com/Icinga/icingaweb2/blob/master/packages/files/apache/icingaweb2.fpm.conf). Put this stanza inside the `<Directory /usr/local/nagvis/share>` directive:

```
  <IfVersion >= 2.4>
    # Forward PHP requests to FPM
    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
    <FilesMatch "\.php$">
      SetHandler "proxy:fcgi://127.0.0.1:9000"
      ErrorDocument 503 /icingaweb2/error_unavailable.html
    </FilesMatch>
  </IfVersion>
```

##### Adjust PHP-FPM php.ini include path

Set the `include_path` to include `/usr/share/php`.

Update `/etc/opt/rh/rh-php71/php.ini`:
```apache
include_path = ".:/usr/share/php:/opt/rh/rh-php71/root/usr/share/pear:/opt/rh/rh-php71/root/usr/share/php"
```

If you do not do this, you will either get a blank screen when you open the NagVis tab, or if errors are configured to be displayed,  you may get the error:
```
Warning: Uncaught Error: (0) require_once(Icinga/Application/EmbeddedWeb.php): failed to open stream: No such file or directory
```

Set 
##### Adjust www.conf PATH environment setting

In order to get NagVis properly integrated with `graphviz` you have to adjust the `www.conf` file to pass a PATH environment variable into `php-fpm` processes. Edit `/etc/opt/rh/rh-php71/php-fpm.d/www.conf` and uncomment the `env[PATH]` line: ([Thanks mj84](https://monitoring-portal.org/t/nagvis-automap-on-centos-7-5/3971)) 

```apache
env[PATH] = /usr/local/bin:/usr/bin:/bin
```

##### Alternative option: Run NagVis via `mod_php` and adjust the `session.save_path`

It is possible to run NagVis via the system PHP packages (these are PHP 5.4 for CentOS 7). This configuration is simpler but has risks associated with running an older PHP version and may be less performant.

If you use the system PHP to run NagVis under `mod_php` running in Apache httpd, you must adjust the session save path to have both PHP runtimes share the same session store.

For a configuration using the system PHP running in Apache httpd using `mod_php`, update `/etc/httpd/conf.d/php.conf`:
```apache
php_value session.save_path    "/var/opt/rh/rh-php71/lib/php/session"
```

#### SELinux on CentOS

If SELinux is enabled, when you visit `/nagvis/` in your browser, you will receive error messages about the cache directory not being writable, for example `/usr/local/nagvis/var/tmpl/cache`. Even though the standard UNIX file permissions would allow the web server user `apache` to read and write in these directories, SELinux mandatory access control rules prevent this. You can confirm this by examining `/var/log/audit/audit.log`, you will see entries similar to:

```
type=AVC msg=audit(1577662574.710:926674): avc:  denied  { write } for  pid=8231 comm="httpd" name="var" dev="nvme0n1p1" ino=18240328 scontext=system_u:system_r:httpd_t:s0 tcontext=unconfined_u:object_r:httpd_sys_content_t:s0 tclass=dir permissive=0
```

To fix this, you must tell the system that it is OK for the web server to perform these operations. These commands will permanently adjust the file labels under the `nagvis` directory to work with `httpd` and PHP-FPM:
```shell
# Ensure you hae the semanage utility installed
yum install -q -y policycoreutils-python
# Adjust NAGVIS_ROOT to be the root of your installation
NAGVIS_ROOT=/usr/local/nagvis
semanage fcontext -a -t httpd_sys_content_t "$NAGVIS_ROOT(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "$NAGVIS_ROOT/var(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "$NAGVIS_ROOT/etc(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "$NAGVIS_ROOT/share/userfiles/(/.*)?"
restorecon -R "$NAGVIS_ROOT"
```

_*WARNING*_:  Many system administrators would advise you to disable SELinux or set it to permissive to work around the problem, however this will weaken the security posture of your system. Other advice might counsel you to change the SELinux policy via `audit2allow` to broaden the permissions. Both of these approaches are flawed and will weaken the security of your system overall. Instead, configure file labels for SELinux in order to tell the system that the `nagvis` directories are related to `httpd`, and some of them are OK for the web server to write to. 


