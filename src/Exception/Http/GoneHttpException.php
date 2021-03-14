<?php

namespace Mangadex\Exception\Http;

use Mangadex\Exception\Http\HttpException;

class GoneHttpException extends HttpException
{
    function __construct($message = "Gone")
    {
        parent::__construct($message, 410);
    }
}
