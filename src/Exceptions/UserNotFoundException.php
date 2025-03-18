<?php

namespace Trinavo\Ownable\Exceptions;

use Exception;

class UserNotSetException extends Exception
{
    public function __construct(string $message = "User not set", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 