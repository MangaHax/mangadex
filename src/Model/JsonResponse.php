<?php

namespace Mangadex\Model;

class JsonResponse
{
    private $code;
    private $data;
    private $message;

    function __construct($code = 200, $message = '')
    {
        $this->setCode($code);
        $this->setMessage($message);
    }

    function getCode()
    {
        return $this->code;
    }

    function setCode($code)
    {
        $this->code = $code;
    }

    function getData()
    {
        return $this->data;
    }

    function setData($data)
    {
        $this->data = $data;
    }

    function getMessage()
    {
        return $this->message;
    }

    function setMessage($message)
    {
        $this->message = $message;
    }

    function normalize()
    {
        $normalized = [
            'code' => $this->getCode(),
            'status' => !empty($this->getCode()) && $this->getCode() < 400 ? 'OK' : 'error',
        ];
        if (!empty($this->getMessage())) {
            $normalized['message'] = $this->getMessage();
        }
        if ($this->getData() !== null) {
            $normalized['data'] = $this->getData();
        }
        return $normalized;
    }
}
