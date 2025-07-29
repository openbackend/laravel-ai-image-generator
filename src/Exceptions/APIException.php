<?php

namespace OpenBackend\AiImageGenerator\Exceptions;

/**
 * Exception thrown when API request fails
 */
class APIException extends AIImageGeneratorException
{
    protected int $statusCode;
    protected array $responseData;

    public function __construct(string $message, int $statusCode = 0, array $responseData = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->responseData = $responseData;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }
}
