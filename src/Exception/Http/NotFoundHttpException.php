<?php

namespace Mangadex\Exception\Http;

use Mangadex\Exception\Http\HttpException;

class NotFoundHttpException extends HttpException
{
    function __construct($message = "Not Found")
    {
        parent::__construct($message, 404);
    }
}
