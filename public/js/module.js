// SPDX-FileCopyrightText: 2018 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

(function(Icinga) {

    var Nagvis = function(module) {

        this.module = module;

        this.idCache = {};

        this.initialize();

        this.module.icinga.logger.debug('Nagvis module loaded');
    };

    Nagvis.prototype = {

        initialize: function()
        {
            $('#nagvis-iframe').on('load', this.frameLoaded.bind(this));
        },

        frameLoaded: function (event) {
            var icinga = this.module.icinga;
            var frame = event.currentTarget;
            icinga.logger.debug('Nagvis frame loaded');

            if (! frame || ! frame.contentWindow) {
                return;
            }

            var frameUrl;
            try {
                frameUrl = new URL(frame.contentWindow.location.href);
            } catch (e) {
                // A different-origin NagVis installation cannot expose its URL to us.
                icinga.logger.debug('Nagvis frame location is not accessible; skipping map sync');
                return;
            }

            if (! /\/frontend\/nagvis-js\/index\.php$/.test(frameUrl.pathname)) {
                return;
            }

            var params = frameUrl.searchParams;
            if (params.get('mod') !== 'Map') {
                return;
            }

            var parentParams = new URLSearchParams(window.location.search);
            var currentMap = params.get('show');
            var shownMap = parentParams.get('map');
            if (currentMap === null || currentMap === '') {
                return;
            }

            if (shownMap !== currentMap) {
                // Reload the parent first: its controller will render the new map
                // with the requested header_menu value.
                this.setCurrentMap(currentMap);
                return;
            }

            // NagVis's own map links can omit header_menu. When the selected map
            // stays the same, the parent does not reload and its Show/Hide control
            // otherwise becomes inconsistent with the menu actually in the iframe.
            var desiredMenu = parentParams.get('showMenu') === '1' ? '1' : '0';
            if (params.get('header_menu') !== desiredMenu) {
                params.set('header_menu', desiredMenu);
                icinga.logger.debug('Restoring Nagvis menu state', desiredMenu);
                // Replace the iframe entry, not the parent page or its history.
                // The next load sees the correct parameter and does not reload.
                frame.contentWindow.location.replace(frameUrl.toString());
            }
        },

        setCurrentMap: function (map) {
            var url = new URL(window.location.href);
            url.searchParams.set('map', map);
            this.module.icinga.logger.info('Setting current map', map);
            // Preserve showMenu when reloading, so the NagVis iframe also gets
            // the requested header_menu setting for the newly selected map.
            window.location.assign(url.toString());
        }

    };

    Icinga.availableModules.nagvis = Nagvis;

}(Icinga));

