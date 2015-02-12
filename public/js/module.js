
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
            var currentMap = null;
            var shownMap = null;

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
//icinga.logger.info(icinga.utils.addUrlParams(document.location.pathname + '?' + document.location.search, { map: 'asd' }))
//            var url = parseUrl
            this.module.icinga.logger.info("Setting current map", map);
        }

    };

    Icinga.availableModules.nagvis = Nagvis;

}(Icinga));

