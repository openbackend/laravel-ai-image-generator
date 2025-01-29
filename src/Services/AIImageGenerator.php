<?php

namespace Rudraramesh\LaravelAiImageGenerator\Services;

use GuzzleHttp\Client;

class AIImageGenerator
{
    protected $client;
    protected $apiKey;
    protected $apiEndpoint;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('AI_API_KEY');
        $this->apiEndpoint = env('AI_API_ENDPOINT');
    }

    public function generate($prompt)
    {
        $response = $this->client->post($this->apiEndpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'prompt' => $prompt,
                'n' => 1,
                'size' => '1024x1024'
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        return $body['data'][0]['url'] ?? null; // URL of the generated image
    }
}
