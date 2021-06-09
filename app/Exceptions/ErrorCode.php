<?php

namespace App\Exceptions;

class ErrorCode
{
    const DEFAULT = 40000;
    const UNAUTHORIZED = 40100;
    const FORBIDDEN = 40300;
    const NOT_FOUND = 40400;
    const MODEL_NOT_FOUND = 40401;
    const HTTP_NOT_FOUND = 40402;
    const METHOD_NOT_ALLOWED = 40500;
    const UNPROCESSABLE_ENTITY = 42200;
    const TOO_MANY_REQUEST = 42900;
    const INTERNAL_SERVER_ERROR = 50000;
    const SQL_ERROR = 60000;

    const ErrorMsg = [
        self::DEFAULT => 'Default error',
        self::UNAUTHORIZED => 'Unauthenticated',
        self::FORBIDDEN => 'This action is unauthorized',
        self::NOT_FOUND => 'Not Found',
        self::MODEL_NOT_FOUND => 'Model Not Found',
        self::HTTP_NOT_FOUND => 'HTTP Not Found',
        self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
        self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::SQL_ERROR => 'Query error',
        self::TOO_MANY_REQUEST => 'Too Many Attempts',
    ];
}
