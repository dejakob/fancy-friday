<?php

$app = FancyFridayApi::getInstance();
$app->route();

class FancyFridayApi
{
    private static $instance = null;
    private $get;
    private $post;
    private $action;

    private function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
    }

    public function route ()
    {
        if (isset($this->get['action'])) {
            $this->action = $this->get['action'];

            switch ($this->action) {
                case 'forward':
                    if (isset($this->get['url']))
                        ForwardAction::run($this->get['url']);
                    break;

                case 'spotify_me':
                    if (isset($this->get['access_token']))
                        $meData = SpotifyAction::me($this->get['access_token']);
                        SpotifyAction::tryToSync(json_decode($meData), $this->get['access_token']);
                        die($meData);
                    break;

                case 'spotify_add_track':
                    if (isset($this->get['access_token']) && isset($this->get['track_uri']))
                        die(SpotifyAction::addTrackToPlaylist($this->get['access_token'], $this->get['track_uri']));
                    break;
            }
        }

        die('nothing to see here...');
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}


class ForwardAction
{
    public static function run ($url)
    {
        $fileContents = file_get_contents($url);
        die($fileContents);
    }
}

class SpotifyAction
{
    private static $userIdOfPlaylist = '116923018';
    private static $cacheFile = '../cache/playlist_curl.txt';
    private static $playlistId = '4wZgosFI8FOXfrn0jtqt0v';

    public static function me ($accessToken)
    {
        return shell_exec('curl -X GET "https://api.spotify.com/v1/me" -H "Authorization: Bearer ' . urlencode($accessToken) . '"');
    }

    public static function addTrackToPlaylist ($accessToken, $trackUri)
    {
        $me = json_decode(SpotifyAction::me($accessToken));
        $meId = $me->id;

        $userIdOfPlaylist = self::$userIdOfPlaylist;
        $playlistId = self::$playlistId;

        $curlRequest = 'curl -i -X POST "https://api.spotify.com/v1/users/' . $userIdOfPlaylist . '/playlists/' . $playlistId . '/tracks?uris=' . urlencode($trackUri) . '" -H "Authorization: Bearer ' . urlencode($accessToken) . '" -H "Accept: application/json";';

        if ($meId === $userIdOfPlaylist) {
            return shell_exec($curlRequest);
        }

        file_put_contents(self::$cacheFile, urlencode($trackUri) . ' ', FILE_APPEND);

        $result = array();
        $result['success'] = true;

        return json_encode($result);
    }

    public static function tryToSync ($me, $accessToken)
    {
        $meId = $me->id;

        if ($meId === self::$userIdOfPlaylist) {
            try {
                $trackUri = trim(file_get_contents(self::$cacheFile));
            }
            catch (Exception $ex) {
                $trackUri = '';
            }

            if (mb_strlen($trackUri) > 1) {
                $trackUris = str_replace(' ', ',', $trackUri);
                $curlRequest = 'curl -i -X POST "https://api.spotify.com/v1/users/' . self::$userIdOfPlaylist . '/playlists/' . self::$playlistId . '/tracks?uris=' . $trackUris . '" -H "Authorization: Bearer ' . urlencode($accessToken) . '" -H "Accept: application/json";';
                shell_exec($curlRequest);
                file_put_contents(self::$cacheFile, '');
            }
        }
    }
}