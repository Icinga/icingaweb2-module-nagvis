# Module - Configuration, Icingaweb2 Module nagvis
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

## Config entries

The config has two sections. A `[global]` section and a `[menu]` section.

The `[global]` contains global entries for this icingaweb2-module-nagvis. The `[menu]` section contains mappings from NagVis maps to menu entries in `Maps`.



