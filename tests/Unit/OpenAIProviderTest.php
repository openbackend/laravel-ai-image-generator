<?php

namespace OpenBackend\AiImageGenerator\Tests\Unit;

use OpenBackend\AiImageGenerator\Exceptions\InvalidPromptException;
use OpenBackend\AiImageGenerator\Providers\OpenAIProvider;
use OpenBackend\AiImageGenerator\Tests\TestCase;

class OpenAIProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_validate_prompts(): void
    {
        $config = [
            'api_key' => 'test-key',
            'api_url' => 'https://api.openai.com/v1',
            'model' => 'dall-e-3',
        ];

        $provider = new OpenAIProvider($config);

        // Valid prompt
        $this->assertTrue($provider->validatePrompt('A beautiful sunset'));

        // Empty prompt should fail
        $this->expectException(InvalidPromptException::class);
        $provider->validatePrompt('');
    }

    /** @test */
    public function it_returns_correct_supported_sizes(): void
    {
        $config = [
            'api_key' => 'test-key',
            'api_url' => 'https://api.openai.com/v1',
            'model' => 'dall-e-3',
        ];

        $provider = new OpenAIProvider($config);
        $sizes = $provider->getSupportedSizes();

        $this->assertIsArray($sizes);
        $this->assertArrayHasKey('dall-e-3', $sizes);
        $this->assertContains('1024x1024', $sizes['dall-e-3']);
    }

    /** @test */
    public function it_returns_correct_provider_name(): void
    {
        $config = [
            'api_key' => 'test-key',
            'api_url' => 'https://api.openai.com/v1',
            'model' => 'dall-e-3',
        ];

        $provider = new OpenAIProvider($config);
        $this->assertEquals('openai', $provider->getName());
    }

    /** @test */
    public function it_checks_availability_correctly(): void
    {
        $configWithKey = [
            'api_key' => 'test-key',
            'api_url' => 'https://api.openai.com/v1',
        ];

        $configWithoutKey = [
            'api_key' => '',
            'api_url' => 'https://api.openai.com/v1',
        ];

        $providerWithKey = new OpenAIProvider($configWithKey);
        $this->assertTrue($providerWithKey->isAvailable());

        $providerWithoutKey = new OpenAIProvider($configWithoutKey);
        $this->assertFalse($providerWithoutKey->isAvailable());
    }
}
