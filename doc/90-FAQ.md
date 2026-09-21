# FAQ

## URLs to Icinga Web 2 views do not work

Ensure that `urltarget = "_top"` is set inside the `default` section
in the `nagvis.ini.php` configuration file.

## Map Path demo-overview.cfg doesn't exist

The NagVis module displays `demo-overview` by default. To use an existing map instead,
set `default-map` in the **Icinga Web 2 NagVis module configuration** at
`<ICINGAWEB_CONFIGDIR>/modules/nagvis/config.ini` (typically
`/etc/icingaweb2/modules/nagvis/config.ini`):

```ini
[global]
default-map = my-map
```

Replace `my-map` with the name of your map (without the `.cfg` extension).
Do **not** add `default-map` to NagVis' `nagvis.ini.php`: it is an Icinga Web 2
module option, not a NagVis configuration option.

An explicit `map` URL parameter takes precedence over this setting.
