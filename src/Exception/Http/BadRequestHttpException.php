<?php

namespace Mangadex\Exception\Http;

use Mangadex\Exception\Http\HttpException;

class BadRequestHttpException extends HttpException
{
    function __construct($message = "Bad Request")
    {
        parent::__construct($message, 400);
    }
}
