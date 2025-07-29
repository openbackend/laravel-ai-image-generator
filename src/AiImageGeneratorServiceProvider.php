<?php

namespace OpenBackend\AiImageGenerator;

use Illuminate\Support\ServiceProvider;
use OpenBackend\AiImageGenerator\Services\AIImageGenerator;

class AiImageGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/config/aiimagegenerator.php',
            'aiimagegenerator'
        );

        // Register the main service
        $this->app->singleton('ai-image-generator', function () {
            return new AIImageGenerator();
        });

        // Register the service with a more descriptive binding
        $this->app->bind(AIImageGenerator::class, function () {
            return app('ai-image-generator');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/config/aiimagegenerator.php' => config_path('aiimagegenerator.php'),
        ], 'ai-image-generator-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'ai-image-generator-migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \OpenBackend\AiImageGenerator\Console\Commands\GenerateImageCommand::class,
                \OpenBackend\AiImageGenerator\Console\Commands\ListProvidersCommand::class,
            ]);
        }

        // Publish assets (if any)
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/ai-image-generator'),
        ], 'ai-image-generator-views');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ai-image-generator');

        // Load routes (if needed)
        // $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'ai-image-generator',
            AIImageGenerator::class,
        ];
    }
}
