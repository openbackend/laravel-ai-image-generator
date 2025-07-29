<?php

namespace OpenBackend\AiImageGenerator\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use OpenBackend\AiImageGenerator\AiImageGeneratorServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();
    }

    protected function getPackageProviders($app): array
    {
        return [
            AiImageGeneratorServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set test configuration
        config()->set('aiimagegenerator.providers.openai.api_key', 'test-key');
        config()->set('aiimagegenerator.database.enabled', true);
        config()->set('aiimagegenerator.rate_limiting.enabled', false);
    }
}
