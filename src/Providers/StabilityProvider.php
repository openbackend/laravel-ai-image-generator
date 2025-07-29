<?php

namespace OpenBackend\AiImageGenerator\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenBackend\AiImageGenerator\Contracts\AIImageProviderInterface;
use OpenBackend\AiImageGenerator\Exceptions\APIException;
use OpenBackend\AiImageGenerator\Exceptions\ConfigurationException;
use OpenBackend\AiImageGenerator\Exceptions\InvalidPromptException;
use OpenBackend\AiImageGenerator\Models\GeneratedImage;

/**
 * Stability AI Image Provider
 */
class StabilityProvider implements AIImageProviderInterface
{
    protected Client $client;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'timeout' => $config['timeout'] ?? 120,
            'base_uri' => rtrim($config['api_url'] ?? 'https://api.stability.ai/v1', '/') . '/',
        ]);

        if (empty($config['api_key'])) {
            throw new ConfigurationException('Stability AI API key is required');
        }
    }

    public function generate(string $prompt, array $options = []): GeneratedImage
    {
        $this->validatePrompt($prompt);

        $generation = GeneratedImage::create([
            'provider' => $this->getName(),
            'prompt' => $prompt,
            'options' => $options,
            'model' => $options['model'] ?? $this->config['engine'] ?? 'stable-diffusion-xl-1024-v1-0',
            'status' => 'pending',
            'user_id' => $options['user_id'] ?? null,
            'session_id' => $options['session_id'] ?? session()->getId(),
        ]);

        try {
            $response = $this->makeRequest($prompt, $options);
            
            if (!isset($response['artifacts'][0]['base64'])) {
                throw new APIException('No image data received from Stability AI');
            }

            $imageData = base64_decode($response['artifacts'][0]['base64']);
            $imageUrl = $this->storeImage($imageData, $generation);

            $generation->update([
                'image_url' => $imageUrl,
                'status' => 'completed',
                'metadata' => [
                    'response_data' => $response,
                    'seed' => $response['artifacts'][0]['seed'] ?? null,
                    'finish_reason' => $response['artifacts'][0]['finishReason'] ?? null,
                ],
            ]);

            return $generation->fresh();

        } catch (\Exception $e) {
            $generation->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            if (config('aiimagegenerator.logging.log_errors', true)) {
                Log::error('Stability AI image generation failed', [
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
            'model' => $options['model'] ?? $this->config['engine'] ?? 'stable-diffusion-xl-1024-v1-0',
            'status' => 'pending',
            'user_id' => $options['user_id'] ?? null,
            'session_id' => $options['session_id'] ?? session()->getId(),
        ]);

        return $generation;
    }

    public function getSupportedSizes(): array
    {
        return [
            'square' => ['1024x1024', '512x512'],
            'portrait' => ['768x1344', '768x1152', '832x1216'],
            'landscape' => ['1344x768', '1152x768', '1216x832'],
        ];
    }

    public function getSupportedModels(): array
    {
        return [
            'stable-diffusion-xl-1024-v1-0',
            'stable-diffusion-v1-6',
            'stable-diffusion-512-v2-1',
        ];
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

        return true;
    }

    public function getMaxPromptLength(): int
    {
        return 2000; // Stability AI's limit
    }

    public function getName(): string
    {
        return 'stability';
    }

    public function isAvailable(): bool
    {
        return !empty($this->config['api_key']);
    }

    protected function makeRequest(string $prompt, array $options = []): array
    {
        $engine = $options['model'] ?? $this->config['engine'] ?? 'stable-diffusion-xl-1024-v1-0';
        
        // Parse size option
        $size = $options['size'] ?? '1024x1024';
        [$width, $height] = explode('x', $size);

        $payload = [
            'text_prompts' => [
                [
                    'text' => $prompt,
                    'weight' => 1.0
                ]
            ],
            'cfg_scale' => $options['cfg_scale'] ?? 7,
            'height' => (int) $height,
            'width' => (int) $width,
            'samples' => 1,
            'steps' => $options['steps'] ?? 30,
        ];

        // Add negative prompt if provided
        if (!empty($options['negative_prompt'])) {
            $payload['text_prompts'][] = [
                'text' => $options['negative_prompt'],
                'weight' => -1.0
            ];
        }

        // Add seed if provided
        if (!empty($options['seed'])) {
            $payload['seed'] = (int) $options['seed'];
        }

        try {
            $response = $this->client->post("generation/{$engine}/text-to-image", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config['api_key'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '';
            
            // Parse error response if available
            $errorData = null;
            if ($responseBody) {
                $errorData = json_decode($responseBody, true);
            }
            
            // Provide more helpful error messages
            $errorMessage = 'Stability AI API request failed: ';
            
            if ($statusCode === 401) {
                $errorMessage = 'Invalid Stability AI API key. Please check your API key configuration.';
            } elseif ($statusCode === 402) {
                $errorMessage = 'Stability AI account has insufficient credits. Please add credits to your account.';
            } elseif ($statusCode === 429) {
                $errorMessage = 'Stability AI API rate limit exceeded. Please try again later.';
            } elseif ($statusCode === 400) {
                if ($errorData && isset($errorData['message'])) {
                    $errorMessage = "Stability AI API error: " . $errorData['message'];
                } else {
                    $errorMessage = 'Invalid request to Stability AI API. Please check your prompt and options.';
                }
            } else {
                $errorMessage .= $e->getMessage();
            }

            Log::error('Stability AI API request failed', [
                'status_code' => $statusCode,
                'error_data' => $errorData,
                'response_body' => $responseBody,
                'request_payload' => $payload,
            ]);
            
            throw new APIException(
                $errorMessage,
                $statusCode,
                $errorData ?: []
            );
        }
    }

    protected function storeImage(string $imageData, GeneratedImage $generation): string
    {
        $fileName = sprintf(
            'stability_%s_%s.png',
            $generation->id,
            time()
        );

        $storagePath = config('aiimagegenerator.storage.path', 'ai-generated-images');
        $filePath = $storagePath . '/' . $fileName;

        $disk = config('aiimagegenerator.storage.disk', 'public');
        Storage::disk($disk)->put($filePath, $imageData);

        $generation->update([
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => strlen($imageData),
            'mime_type' => 'image/png',
        ]);

        // Return a data URL for now (can be enhanced to return proper URL)
        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    protected function checkContentFilter(string $prompt): void
    {
        // Basic content filtering - can be enhanced
        $bannedWords = ['violence', 'gore', 'explicit', 'nsfw'];
        
        foreach ($bannedWords as $word) {
            if (stripos($prompt, $word) !== false) {
                throw new InvalidPromptException('Prompt contains inappropriate content');
            }
        }
    }
}
