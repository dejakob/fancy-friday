(function () {
    'use strict';

    angular
        .module('FancyFriday', [])
        .config(function($sceDelegateProvider) {
        $sceDelegateProvider.resourceUrlWhitelist([
            "self",
            /(mp3|ogg)$/,
        ]);
    });

    angular
        .module('FancyFriday')
        .controller('MainController', MainController);

    function MainController ($scope, $http)
    {
        var SPOTIFY_CLIENT_ID = '6dd0e38dc24a4f9494881679032d442a';

        var vm = this;

        vm.currentTrackSearch = '';

        vm.init = init;
        vm.login = login;
        vm.search = search;

        init();

        function init ()
        {
            if (window.location.href.match(/.*access_token=.*&.*/gi)) {
                vm.spotifyAccessToken = window.location.href.split('access_token=')[1].split('&')[0];
                search('taylor', true);
            }

            $http({
                method: 'GET',

                // type: track / album / playlist
                url: './api/?action=spotify_me&access_token=' + vm.spotifyAccessToken
            }).success(function (data) {console.log(data)});
        }

        function login ()
        {
            window.location.href = getLoginUrl(['playlist-read-private', 'playlist-modify', 'playlist-modify-private']);
        }

        function search (query, force)
        {
            searchTrack(query || vm.currentTrackSearch, function (data) {
                if (data.tracks) {
                    vm.searchResults = data.tracks.items;
                }
            }, force);
        }

        function searchTrack (query, cb, force)
        {
            $http({
                method: 'GET',
                // type: track / album / playlist
                url: forwardUrl('https://api.spotify.com/v1/search?q=' + encodeURIComponent(query) + '&type=track')
            })
                .success(function () { if (vm.currentTrackSearch === query || force) cb.apply(this, arguments) });
        }

        function forwardUrl (url)
        {
            return './api/?action=forward&url=' + encodeURIComponent(url);
        }

        function getLoginUrl (scopes)
        {
            return 'https://accounts.spotify.com/authorize?client_id=' + SPOTIFY_CLIENT_ID +
                '&redirect_uri=http://fancy-friday.dev' +
                '&scope=' + encodeURIComponent(scopes.join(' ')) +
                '&response_type=token';
        }
    }

    angular
        .module('FancyFriday')
        .directive('easyAudio', EasyAudioDirective);

    function EasyAudioDirective ()
    {
        return {
            link: link,
            scope: {
                source: '=?'
            },
            restrict: 'AE'
        };

        function link (scope, element, attrs) {
            console.log('LINNK');

            scope.$watch('source', function (source) {
                element[0].innerHTML = '<audio controls="false" autoplay><source src="' + source + '"></audio>';
            });
        }
    }

})();