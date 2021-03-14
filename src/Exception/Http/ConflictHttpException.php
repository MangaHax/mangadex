<?php

namespace Mangadex\Exception\Http;

use Mangadex\Exception\Http\HttpException;

class ConflictHttpException extends HttpException
{
    function __construct($message = "Conflict")
    {
        parent::__construct($message, 409);
    }
}
