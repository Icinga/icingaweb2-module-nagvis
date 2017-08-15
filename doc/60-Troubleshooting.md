# Troubleshooting

## Clicking a link inside a nagvis map shows the icingaweb2 blow the actual icingaweb2

Make sure that in the file `<nagvis>/nagvis.ini.php` by section `default` the parameter `urltarget = "_top"` is set.

## Map Path demo-overview.cfg doesn't exist

When running the left Menu "Maps" I god the error message

     The path "/etc/nagvis/maps/demo-overview.cfg" does not exist.

Fix in the file `<ICINGAWEB_CONFDIR>/modules/nagvis/config.ini` inside the global section the parameter default-map.

## More

TDB
