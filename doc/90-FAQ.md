# FAQ

## URLs to Icinga Web 2 views do not work

Ensure that `urltarget = "_top"` is set inside the `default` section
in the `nagvis.ini.php` configuration file.

## Map Path demo-overview.cfg doesn't exist

Specify a different `default-map` in the `nagvis.ini.php` configuration file.

## NagVis URLs conflict when Icinga Web 2 is served from the web root

When Icinga Web 2 is served from `/`, its NagVis module uses URLs such as
`/nagvis/show/map`. A standalone NagVis installation served from `/nagvis`
can intercept those URLs, causing the Icinga Web 2 module route to fail.

The conflict can also happen in the **opposite direction**: if a request for
the standalone NagVis frontend, for example
`/nagvis/frontend/nagvis-js/index.php?mod=Map&act=view&show=demo-overview`,
gets rewritten or redirected to Icinga Web 2, the iframe may show a second
Icinga Web 2 sidebar instead of a NagVis map.

If you see nested or duplicated sidebars, inspect the iframe's URL and
response in your browser's developer tools. Requests for
`/nagvis/frontend/nagvis-js/index.php` must reach the standalone NagVis
application, while `/icingaweb2/nagvis/show/map` (for an Icinga Web 2
installation under `/icingaweb2`) must reach the Icinga Web 2 module.
Check web-server aliases, rewrite rules and redirects if either request
is served by the wrong application.

Configure the web server so that Icinga Web 2 and standalone NagVis use
**distinct URL paths**. For example, keep Icinga Web 2 at `/` and serve the
standalone NagVis application from `/nagvis-app`. Then set its URL in
`<ICINGAWEB_CONFIGDIR>/modules/nagvis/config.ini`:

```ini
[global]
baseurl = /nagvis-app
```

The `baseurl` option controls the standalone NagVis URL loaded inside the
module's iframe. It does **not** change the Icinga Web 2 module route
(`/nagvis/show/map`) or configure the web server. Moving the standalone
application to another path must be done in your web server configuration.

Alternatively, serve Icinga Web 2 under a separate base path such as
`/icingaweb2` and adjust the NagVis integration URLs (including
`[paths] htmlcgi` in `nagvis.ini.php`) to match your deployment.
