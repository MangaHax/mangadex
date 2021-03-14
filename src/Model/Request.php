<?php

namespace Mangadex\Model;

use Exception;

class Request
{
    private static $instance;
    private $content;

    public $query;
    public $request;
    public $server;
    public $headers;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self($_GET, $_POST, $_SERVER);
        }

        return self::$instance;
    }

    function __construct($query = [], $request = [], $server = [])
    {
        $this->query = new ParameterBag($query);
        $this->request = new ParameterBag($request);

        $server['REQUEST_METHOD'] = strtoupper($server['REQUEST_METHOD']);
        $this->server = new ParameterBag($server);

        $headers = [];
        foreach ($server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[substr($key, 5)] = $value;
            } else if (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headers[$key] = $value;
            }
        }
        $this->headers = new ParameterBag($headers);
    }

    public function getMethod()
    {
        return $this->server->get('REQUEST_METHOD', 'GET');
    }

    public function getContentType()
    {
        return $this->headers->get('CONTENT_TYPE');
    }

    public function getContent()
    {
        if ($this->content === null) {
            $this->content = file_get_contents('php://input');
        }
        return $this->content;
    }
}
