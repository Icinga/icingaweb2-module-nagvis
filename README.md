# Icinga Web 2 Nagvis Module
## NagVis Configuration
This module provides everything for a complete Icinga Web 2 NagVis
integration, including authentication and authorisation. To get
everything working as expected, your NagVis configuration should contain
the following settings:

```ini
[global]
authmodule="CoreAuthModIcingaweb2"
authorisationmodule="CoreAuthorisationModIcingaweb2"
logonmodule="LogonIcingaweb2"

[paths]
htmlcgi = "/icingaweb"

[defaults]
backend = "ndomy_1"
urltarget = "_top"
hosturl = "[htmlcgi]/monitoring/show/host?host=[host_name]"
hostgroupurl = "[htmlcgi]/monitoring/show/hostgroup?hostgroup=[hostgroup_name]"
serviceurl = "[htmlcgi]/monitoring/show/service?host=[host_name]&service=[service_description]"
servicegroupurl = "[htmlcgi]/monitoring/show/servicegroup?servicegroup=[servicegroup_name]"
headermenu="0"
```



## Module Configuration
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
