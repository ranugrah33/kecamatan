<?php

namespace App\Services\AI\Exceptions;

use Exception;

class AIServiceException extends Exception
{
    protected string $provider;
    protected int $statusCode;

    public function __construct(string $provider, string $message, int $statusCode = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->provider = $provider;
        $this->statusCode = $statusCode;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

