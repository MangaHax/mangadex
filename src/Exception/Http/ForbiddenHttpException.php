<?php

namespace Mangadex\Exception\Http;

use Mangadex\Exception\Http\HttpException;

class ForbiddenHttpException extends HttpException
{
    function __construct($message = "Forbidden")
    {
        parent::__construct($message, 403);
    }
}
