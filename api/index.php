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