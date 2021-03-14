<?php

namespace Mangadex\Controller\API;

use Mangadex\Exception\Http\BadRequestHttpException;
use Mangadex\Exception\Http\MethodNotAllowedHttpException;
use Mangadex\Model\JsonResponse;
use Mangadex\Model\Request;


abstract class APIController
{

    protected $request;
    protected $mdAtHomeClient;
    protected $user;

    function __construct()
    {
        /** @param object $mdAtHomeClient */
        global $mdAtHomeClient;
        /** @param object $user */
        global $user;

        $this->request = Request::getInstance();
        $this->mdAtHomeClient = $mdAtHomeClient;
        $this->user = $user;
    }

    protected function validateId($id)
    {
        if (!is_numeric($id) || !$id) {
            throw new BadRequestHttpException("No valid ID provided.");
        }
        return (int)$id;
    }

    public function handleRequest($path)
    {
        $res = new JsonResponse();

        switch ($this->request->getMethod()) {
            case 'GET':
                $res->setData($this->view($path));
                break;
            case 'POST':
                $res->setData($this->create($path));
                break;
            case 'PUT':
                if ($this->request->getContentType() !== "application/json") {
                    throw new BadRequestHttpException("Content-Type must be application/json.");
                }
                $res->setData($this->update($path));
                break;
            case 'DELETE':
                $res->setData($this->delete($path));
                break;
        }

        return $res;
    }

    public function view($path)
    {
        throw new MethodNotAllowedHttpException();
    }

    public function create($path)
    {
        throw new MethodNotAllowedHttpException();
    }

    public function update($path)
    {
        throw new MethodNotAllowedHttpException();
    }

    public function delete($path)
    {
        throw new MethodNotAllowedHttpException();
    }

    protected function decodeJSONContent()
    {
        if ($this->request->getContentType() !== 'application/json') {
            throw new BadRequestHttpException("Content-Type must be application/json.");
        }
        $content = @json_decode($this->request->getContent());
        if ($content === null) {
            throw new BadRequestHttpException("Request body must be valid JSON.");
        }
        return $content;
    }

    protected function getFileUrl($filePath)
    {
        $timestamp = @filemtime(ABSPATH . $filePath);
        return LOCAL_SERVER_URL . $filePath . ($timestamp ? "?$timestamp" : "");
    }

    protected function validateJSONContent($validators, $content)
    {
        $failed = $this->validateObject($validators, $content);
        if (!empty($failed)) {
            $keys = implode(', ', array_map(function ($s) {
                return "'$s'";
            }, $failed));
            throw new BadRequestHttpException("JSON body parameter(s) $keys required.");
        }
    }

    protected function validateObject($validators, object $obj)
    {
        $failed = [];
        foreach ($validators as $key => $type) {
            switch ($type) {
                case 'int':
                    if (!is_int($obj->$key ?? null))
                        $failed[] = $key;
                    break;
                case 'numeric':
                    if (!is_numeric($obj->$key ?? null))
                        $failed[] = $key;
                    break;
                case 'array':
                    if (!is_array($obj->$key ?? null))
                        $failed[] = $key;
                    break;
                case 'bool':
                    if (!is_bool($obj->$key ?? null))
                        $failed[] = $key;
                    break;
                case 'string':
                    if (!is_string($obj->$key ?? null))
                        $failed[] = $key;
                    break;
            }
        }
        return $failed;
    }
}
