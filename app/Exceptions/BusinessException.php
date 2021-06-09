<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    /**
     * BusinessException constructor.
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message = 'Business error', int $code = ErrorCode::DEFAULT)
    {
        parent::__construct($message, $code, null);
    }
}
