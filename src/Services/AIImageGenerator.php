<?php

namespace OpenBackend\AiImageGenerator\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use OpenBackend\AiImageGenerator\Contracts\AIImageProviderInterface;
use OpenBackend\AiImageGenerator\Exceptions\ConfigurationException;
use OpenBackend\AiImageGenerator\Exceptions\RateLimitException;
use OpenBackend\AiImageGenerator\Models\GeneratedImage;
use OpenBackend\AiImageGenerator\Providers\OpenAIProvider;
use OpenBackend\AiImageGenerator\Providers\StabilityProvider;

/**
 * Main AI Image Generator Service
 */
class AIImageGenerator
{
    protected ?AIImageProviderInterface $provider = null;
    protected string $defaultProvider;
    protected array $config;

    public function __construct()
    {
        $this->config = Config::get('aiimagegenerator', []);
        $this->defaultProvider = $this->config['default'] ?? 'openai';
    }

    /**
     * Set the AI provider to use
     */
    public function provider(?string $provider = null): self
    {
        $providerName = $provider ?? $this->defaultProvider;
        $this->provider = $this->createProvider($providerName);
        return $this;
    }

    /**
     * Get the current provider instance
     */
    public function getProviderInstance(?string $provider = null): AIImageProviderInterface
    {
        if ($provider) {
            return $this->createProvider($provider);
        }
        
        return $this->provider ?? $this->createProvider($this->defaultProvider);
    }

    /**
     * Generate an image from a prompt
     */
    public function generate(string $prompt, array $options = []): GeneratedImage
    {
        $this->checkRateLimit();
        
        $provider = $this->provider ?? $this->createProvider($this->defaultProvider);
        
        if (Config::get('aiimagegenerator.logging.log_requests', true)) {
            Log::info('AI Image generation requested', [
                'provider' => $provider->getName(),
                'prompt' => $prompt,
                'options' => $options,
            ]);
        }

        $result = $provider->generate($prompt, $options);
        
        $this->incrementRateLimit();
        
        return $result;
    }

    /**
     * Generate an image asynchronously
     */
    public function generateAsync(string $prompt, array $options = []): GeneratedImage
    {
        $this->checkRateLimit();
        
        $provider = $this->provider ?? $this->createProvider($this->defaultProvider);
        
        if (Config::get('aiimagegenerator.logging.log_requests', true)) {
            Log::info('AI Image async generation requested', [
                'provider' => $provider->getName(),
                'prompt' => $prompt,
                'options' => $options,
            ]);
        }

        $result = $provider->generateAsync($prompt, $options);
        
        $this->incrementRateLimit();
        
        return $result;
    }

    /**
     * Get list of available providers
     */
    public function getProviders(): array
    {
        return array_keys($this->config['providers'] ?? []);
    }

    /**
     * Get configuration for a specific provider
     */
    public function getProviderConfig(string $provider): array
    {
        return $this->config['providers'][$provider] ?? [];
    }

    /**
     * Validate a prompt
     */
    public function validatePrompt(string $prompt): bool
    {
        $provider = $this->provider ?? $this->createProvider($this->defaultProvider);
        return $provider->validatePrompt($prompt);
    }

    /**
     * Get supported image sizes for a provider
     */
    public function getSupportedSizes(?string $provider = null): array
    {
        $providerInstance = $provider 
            ? $this->createProvider($provider)
            : ($this->provider ?? $this->createProvider($this->defaultProvider));
            
        return $providerInstance->getSupportedSizes();
    }

    /**
     * Get generation history
     */
    public function getGenerationHistory(int $limit = 10): array
    {
        return GeneratedImage::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Delete a generation
     */
    public function deleteGeneration(string $id): bool
    {
        $generation = GeneratedImage::find($id);
        
        if (!$generation) {
            return false;
        }

        // Delete files if they exist
        if ($generation->file_path) {
            $disk = Config::get('aiimagegenerator.storage.disk', 'public');
            \Storage::disk($disk)->delete($generation->file_path);
            
            // Delete thumbnails
            if ($generation->thumbnails) {
                foreach ($generation->thumbnails as $thumbnail) {
                    \Storage::disk($disk)->delete($thumbnail);
                }
            }
        }

        return $generation->delete();
    }

    /**
     * Find a specific generation
     */
    public function findGeneration(string $id): ?GeneratedImage
    {
        return GeneratedImage::find($id);
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats(): array
    {
        $stats = [
            'total_generations' => GeneratedImage::count(),
            'successful_generations' => GeneratedImage::successful()->count(),
            'failed_generations' => GeneratedImage::failed()->count(),
            'pending_generations' => GeneratedImage::pending()->count(),
            'total_cost' => GeneratedImage::sum('cost'),
            'providers_used' => GeneratedImage::distinct('provider')->pluck('provider')->toArray(),
        ];

        // Recent activity (last 30 days)
        $recentStats = GeneratedImage::recent(30);
        $stats['recent_activity'] = [
            'total' => $recentStats->count(),
            'successful' => $recentStats->successful()->count(),
            'failed' => $recentStats->failed()->count(),
            'cost' => $recentStats->sum('cost'),
        ];

        // Provider breakdown
        $stats['provider_breakdown'] = GeneratedImage::selectRaw('provider, COUNT(*) as count, SUM(cost) as total_cost')
            ->groupBy('provider')
            ->get()
            ->keyBy('provider')
            ->toArray();

        return $stats;
    }

    /**
     * Create a provider instance
     */
    protected function createProvider(string $provider): AIImageProviderInterface
    {
        $config = $this->getProviderConfig($provider);
        
        if (empty($config)) {
            throw new ConfigurationException("Provider '{$provider}' is not configured");
        }

        return match ($config['driver'] ?? $provider) {
            'openai' => new OpenAIProvider($config),
            'stability' => new StabilityProvider($config),
            // 'midjourney' => new MidjourneyProvider($config),
            default => throw new ConfigurationException("Unsupported provider driver: {$provider}")
        };
    }

    /**
     * Check rate limiting
     */
    protected function checkRateLimit(): void
    {
        if (!Config::get('aiimagegenerator.rate_limiting.enabled', true)) {
            return;
        }

        $requestsPerMinute = Config::get('aiimagegenerator.rate_limiting.requests_per_minute', 5);
        $requestsPerHour = Config::get('aiimagegenerator.rate_limiting.requests_per_hour', 50);

        $minuteKey = 'ai_image_rate_limit_minute_' . now()->format('Y-m-d-H-i');
        $hourKey = 'ai_image_rate_limit_hour_' . now()->format('Y-m-d-H');

        $minuteCount = Cache::get($minuteKey, 0);
        $hourCount = Cache::get($hourKey, 0);

        if ($minuteCount >= $requestsPerMinute) {
            throw new RateLimitException('Rate limit exceeded: too many requests per minute', 60);
        }

        if ($hourCount >= $requestsPerHour) {
            throw new RateLimitException('Rate limit exceeded: too many requests per hour', 3600);
        }
    }

    /**
     * Increment rate limit counters
     */
    protected function incrementRateLimit(): void
    {
        if (!Config::get('aiimagegenerator.rate_limiting.enabled', true)) {
            return;
        }

        $minuteKey = 'ai_image_rate_limit_minute_' . now()->format('Y-m-d-H-i');
        $hourKey = 'ai_image_rate_limit_hour_' . now()->format('Y-m-d-H');

        Cache::increment($minuteKey);
        Cache::put($minuteKey, Cache::get($minuteKey, 1), 60); // Expire after 1 minute

        Cache::increment($hourKey);
        Cache::put($hourKey, Cache::get($hourKey, 1), 3600); // Expire after 1 hour
    }
}
