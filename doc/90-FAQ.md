# FAQ

## URLs to Icinga Web 2 views do not work

Ensure that `urltarget = "_top"` is set inside the `default` section
in the `nagvis.ini.php` configuration file.

## Map Path demo-overview.cfg doesn't exist

Specify a different `default-map` in the `nagvis.ini.php` configuration file.

## Use the map overview provided by NagVis

You can set `default-map` to `global-overview` to use the built-in overview page from NagVis.
This automatically only shows maps the user has access to.