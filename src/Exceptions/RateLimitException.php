<?php

namespace OpenBackend\AiImageGenerator\Exceptions;

/**
 * Exception thrown when rate limit is exceeded
 */
class RateLimitException extends AIImageGeneratorException
{
    protected int $retryAfter;

    public function __construct(string $message, int $retryAfter = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, 429, $previous);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
