<?php

namespace Mangadex\Exception\Http;

use Mangadex\Exception\Http\HttpException;

class UnavailableForLegalReasonsHttpException extends HttpException
{
    function __construct($message = "Unavailable For Legal Reasons")
    {
        parent::__construct($message, 451);
    }
}
