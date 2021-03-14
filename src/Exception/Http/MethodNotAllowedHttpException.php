<?php

namespace Mangadex\Exception\Http;

use Mangadex\Exception\Http\HttpException;

class MethodNotAllowedHttpException extends HttpException
{
    function __construct($message = "Method Not Allowed")
    {
        parent::__construct($message, 405);
    }
}
