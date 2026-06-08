<?php

namespace App\Exceptions;

use Exception;

class AiServiceException extends Exception
{
    public static function requestFailed(string $message, ?\Throwable $previous = null): self
    {
        return new self($message, 0, $previous);
    }

    public static function invalidResponse(string $message): self
    {
        return new self($message);
    }
}
