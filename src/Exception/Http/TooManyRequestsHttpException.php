<?php

namespace Mangadex\Exception\Http;

use Mangadex\Exception\Http\HttpException;

class TooManyRequestsHttpException extends HttpException
{
    function __construct($message = "Too Many Requests")
    {
        parent::__construct($message, 429);
    }
}
