<?php

namespace OpenBackend\AiImageGenerator\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use OpenBackend\AiImageGenerator\Contracts\AIImageProviderInterface;
use OpenBackend\AiImageGenerator\Exceptions\APIException;
use OpenBackend\AiImageGenerator\Exceptions\ConfigurationException;
use OpenBackend\AiImageGenerator\Exceptions\InvalidPromptException;
use OpenBackend\AiImageGenerator\Models\GeneratedImage;

/**
 * OpenAI DALL-E Image Provider
 */
class OpenAIProvider implements AIImageProviderInterface
{
    protected Client $client;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'timeout' => $config['timeout'] ?? 120,
            'base_uri' => $config['api_url'] ?? 'https://api.openai.com/v1',
        ]);

        if (empty($config['api_key'])) {
            throw new ConfigurationException('OpenAI API key is required');
        }
    }

    public function generate(string $prompt, array $options = []): GeneratedImage
    {
        $this->validatePrompt($prompt);

        $generation = GeneratedImage::create([
            'provider' => $this->getName(),
            'prompt' => $prompt,
            'options' => $options,
            'model' => $options['model'] ?? $this->config['model'] ?? 'dall-e-3',
            'status' => 'pending',
            'user_id' => $options['user_id'] ?? null,
            'session_id' => $options['session_id'] ?? session()->getId(),
        ]);

        try {
            $response = $this->makeRequest($prompt, $options);
            $imageData = $response['data'][0] ?? null;

            if (!$imageData) {
                throw new APIException('No image data received from OpenAI');
            }

            $generation->update([
                'original_url' => $imageData['url'],
                'status' => 'completed',
                'metadata' => [
                    'revised_prompt' => $imageData['revised_prompt'] ?? null,
                    'response_data' => $response,
                ],
            ]);

            // Download and store the image if auto_download is enabled
            if (config('aiimagegenerator.storage.auto_download', true)) {
                $this->downloadAndStore($generation);
            }

            return $generation->fresh();

        } catch (\Exception $e) {
            $generation->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            if (config('aiimagegenerator.logging.log_errors', true)) {
                Log::error('OpenAI image generation failed', [
                    'prompt' => $prompt,
                    'options' => $options,
                    'error' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    public function generateAsync(string $prompt, array $options = []): GeneratedImage
    {
        // For now, we'll just return a pending generation
        // In a real implementation, this would queue a job
        $generation = GeneratedImage::create([
            'provider' => $this->getName(),
            'prompt' => $prompt,
            'options' => $options,
            'model' => $options['model'] ?? $this->config['model'] ?? 'dall-e-3',
            'status' => 'pending',
            'user_id' => $options['user_id'] ?? null,
            'session_id' => $options['session_id'] ?? session()->getId(),
        ]);

        // TODO: Dispatch a job to process this generation
        // dispatch(new GenerateImageJob($generation));

        return $generation;
    }

    public function getSupportedSizes(): array
    {
        return [
            'dall-e-2' => ['256x256', '512x512', '1024x1024'],
            'dall-e-3' => ['1024x1024', '1024x1792', '1792x1024'],
        ];
    }

    public function getSupportedModels(): array
    {
        return ['dall-e-2', 'dall-e-3'];
    }

    public function validatePrompt(string $prompt): bool
    {
        if (empty(trim($prompt))) {
            throw new InvalidPromptException('Prompt cannot be empty');
        }

        if (strlen($prompt) > $this->getMaxPromptLength()) {
            throw new InvalidPromptException(
                sprintf('Prompt cannot exceed %d characters', $this->getMaxPromptLength())
            );
        }

        // Check for inappropriate content if content filter is enabled
        if (config('aiimagegenerator.security.content_filter', true)) {
            $this->checkContentFilter($prompt);
        }

        return true;
    }

    public function getMaxPromptLength(): int
    {
        return 4000; // OpenAI's limit
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function isAvailable(): bool
    {
        $apiKey = $this->config['api_key'] ?? '';
        
        // Check if API key exists and is not a placeholder
        $placeholders = [
            'your-openai-api-key-here',
            'test-key-for-development',
            'sk-test',
            'sk-fake'
        ];
        
        if (empty($apiKey) || in_array($apiKey, $placeholders)) {
            return false;
        }
        
        // Real OpenAI API keys start with 'sk-' and are much longer
        return str_starts_with($apiKey, 'sk-') && strlen($apiKey) > 40;
    }

    protected function makeRequest(string $prompt, array $options): array
    {
        $defaultOptions = $this->config['default_options'] ?? [];
        $requestOptions = array_merge($defaultOptions, $options);

        $payload = [
            'model' => $requestOptions['model'] ?? $this->config['model'] ?? 'dall-e-3',
            'prompt' => $prompt,
            'n' => $requestOptions['n'] ?? 1,
            'size' => $requestOptions['size'] ?? '1024x1024',
            'quality' => $requestOptions['quality'] ?? 'standard',
            'response_format' => $requestOptions['response_format'] ?? 'url',
        ];

        // DALL-E 3 specific options
        if ($payload['model'] === 'dall-e-3') {
            $payload['style'] = $requestOptions['style'] ?? 'vivid';
        }

        try {
            $response = $this->client->post('/images/generations', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config['api_key'],
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '';
            
            // Provide more helpful error messages
            $errorMessage = 'OpenAI API request failed: ';
            
            if ($statusCode === 401) {
                $errorMessage = 'Invalid OpenAI API key. Please check your API key configuration.';
            } elseif ($statusCode === 404) {
                $errorMessage = 'OpenAI API endpoint not found. This might indicate an invalid API key or deprecated endpoint.';
            } elseif ($statusCode === 429) {
                $errorMessage = 'OpenAI API rate limit exceeded. Please try again later.';
            } elseif ($statusCode === 400) {
                $errorMessage = 'Invalid request to OpenAI API. Please check your prompt and options.';
            } else {
                $errorMessage .= $e->getMessage();
            }
            
            throw new APIException(
                $errorMessage,
                $statusCode,
                json_decode($responseBody, true) ?: []
            );
        }
    }

    protected function downloadAndStore(GeneratedImage $generation): void
    {
        if (!$generation->original_url) {
            return;
        }

        try {
            $imageContent = file_get_contents($generation->original_url);
            if ($imageContent === false) {
                throw new \Exception('Failed to download image');
            }

            $fileName = sprintf(
                '%s_%s.png',
                $generation->id,
                time()
            );

            $storagePath = config('aiimagegenerator.storage.path', 'ai-generated-images');
            $filePath = $storagePath . '/' . $fileName;

            $disk = config('aiimagegenerator.storage.disk', 'public');
            \Storage::disk($disk)->put($filePath, $imageContent);

            $generation->update([
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => strlen($imageContent),
                'mime_type' => 'image/png',
            ]);

            // Generate thumbnails if enabled
            if (config('aiimagegenerator.image_processing.thumbnails.enabled', true)) {
                $this->generateThumbnails($generation);
            }

        } catch (\Exception $e) {
            Log::error('Failed to download and store image', [
                'generation_id' => $generation->id,
                'original_url' => $generation->original_url,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function generateThumbnails(GeneratedImage $generation): void
    {
        // TODO: Implement thumbnail generation using Intervention Image
        // This would require the intervention/image package to be installed
    }

    protected function checkContentFilter(string $prompt): void
    {
        // Basic content filtering - in a real implementation,
        // you might use a more sophisticated content filtering service
        $blockedWords = [
            'violent', 'sexual', 'explicit', 'nude', 'naked',
            'gore', 'blood', 'weapon', 'drug', 'hate'
        ];

        $lowercasePrompt = strtolower($prompt);
        foreach ($blockedWords as $word) {
            if (strpos($lowercasePrompt, $word) !== false) {
                throw new InvalidPromptException(
                    'Prompt contains inappropriate content and cannot be processed'
                );
            }
        }
    }
}
