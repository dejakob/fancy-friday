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
                        SpotifyAction::me($this->get['access_token']);
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
    public static function me ($accessToken)
    {
        die(shell_exec('curl -X GET "https://api.spotify.com/v1/me" -H "Authorization: Bearer ' . $accessToken . '"'));
    }
}