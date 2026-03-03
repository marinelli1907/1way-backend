<?php

namespace App\Services\Ai;

class AiException extends \RuntimeException
{
    protected ?int $providerStatusCode;

    public function __construct(string $message, ?int $providerStatusCode = null, ?\Throwable $previous = null)
    {
        $this->providerStatusCode = $providerStatusCode;
        parent::__construct($message, 0, $previous);
    }

    public function getProviderStatusCode(): ?int
    {
        return $this->providerStatusCode;
    }
}
