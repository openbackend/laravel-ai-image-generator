<?php

namespace OpenBackend\AiImageGenerator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * AI Image Generator Facade
 *
 * @method static \OpenBackend\AiImageGenerator\Models\GeneratedImage generate(string $prompt, array $options = [])
 * @method static \OpenBackend\AiImageGenerator\Models\GeneratedImage generateAsync(string $prompt, array $options = [])
 * @method static \OpenBackend\AiImageGenerator\Services\AIImageGenerator provider(string $provider = null)
 * @method static array getProviders()
 * @method static array getProviderConfig(string $provider)
 * @method static bool validatePrompt(string $prompt)
 * @method static array getSupportedSizes(string $provider = null)
 * @method static array getGenerationHistory(int $limit = 10)
 * @method static bool deleteGeneration(string $id)
 * @method static \OpenBackend\AiImageGenerator\Models\GeneratedImage|null findGeneration(string $id)
 * @method static array getUsageStats()
 *
 * @see \OpenBackend\AiImageGenerator\Services\AIImageGenerator
 */
class AIImageGenerator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'ai-image-generator';
    }
}
