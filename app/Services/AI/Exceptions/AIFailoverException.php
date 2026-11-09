<?php

namespace App\Services\AI\Exceptions;

use Exception;

class AIFailoverException extends Exception
{
    protected string $provider;
    protected int $statusCode;
    protected string $reasonCode;

    public function __construct(string $provider, string $message, int $statusCode = 0, string $reasonCode = 'UNKNOWN', ?Exception $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->provider = $provider;
        $this->statusCode = $statusCode;
        $this->reasonCode = $reasonCode;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getReasonCode(): string
    {
        return $this->reasonCode;
    }
}

