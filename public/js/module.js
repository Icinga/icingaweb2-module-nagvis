
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
            var currentMap;
            var icinga = this.module.icinga;
            var $iframe = $('#nagvis-iframe');
            var matchNagvis = /[\?&]show=([^\&]+)/;
            var matchIcinga = /[\?&]map=([^\&]+)/;

            icinga.logger.debug('Nagvis frame loaded');
            if (currentMap = $iframe.contents()[0].location.search.match(matchNagvis)) {
                currentMap = currentMap[1];
            }
            if (shownMap = document.location.search.match(matchIcinga)) {
                shownMap = shownMap[1];
            }
            if (currentMap !== null && shownMap !== currentMap) {
                this.setCurrentMap(currentMap);
            }
        },

        setCurrentMap: function (map) {
            var url = icinga.utils.removeUrlParams(document.location.pathname + document.location.search, [ 'map' ]);
            this.module.icinga.logger.debug('URL AFTER PARAM REMOVE: ' + url);
            url = icinga.utils.addUrlParams(url, { map: map });
            this.module.icinga.logger.info('Setting current map', map);
            location.href = url;
	}

    };

    Icinga.availableModules.nagvis = Nagvis;

}(Icinga));

