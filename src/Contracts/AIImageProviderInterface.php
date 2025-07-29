<?php

namespace OpenBackend\AiImageGenerator\Contracts;

use OpenBackend\AiImageGenerator\Models\GeneratedImage;

/**
 * AI Image Provider Interface
 */
interface AIImageProviderInterface
{
    /**
     * Generate an image from the given prompt
     */
    public function generate(string $prompt, array $options = []): GeneratedImage;

    /**
     * Generate an image asynchronously
     */
    public function generateAsync(string $prompt, array $options = []): GeneratedImage;

    /**
     * Get supported image sizes for this provider
     */
    public function getSupportedSizes(): array;

    /**
     * Get supported models for this provider
     */
    public function getSupportedModels(): array;

    /**
     * Validate the prompt for this provider
     */
    public function validatePrompt(string $prompt): bool;

    /**
     * Get the maximum prompt length for this provider
     */
    public function getMaxPromptLength(): int;

    /**
     * Get the provider name
     */
    public function getName(): string;

    /**
     * Check if the provider is available (API key configured, etc.)
     */
    public function isAvailable(): bool;
}
