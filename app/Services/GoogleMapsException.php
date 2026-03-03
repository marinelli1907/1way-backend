<?php

namespace App\Services;

class GoogleMapsException extends \RuntimeException
{
    protected string $googleStatus;

    public function __construct(string $message = '', string $googleStatus = 'UNKNOWN', ?\Throwable $previous = null)
    {
        $this->googleStatus = $googleStatus;
        parent::__construct($message, 0, $previous);
    }

    public function getGoogleStatus(): string
    {
        return $this->googleStatus;
    }
}
