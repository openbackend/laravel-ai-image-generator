<?php

namespace OpenBackend\AiImageGenerator\Console\Commands;

use Illuminate\Console\Command;
use OpenBackend\AiImageGenerator\Services\AIImageGenerator;

class GenerateImageCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ai-image:generate 
                            {prompt : The prompt for image generation}
                            {--provider= : The AI provider to use (openai, stability, midjourney)}
                            {--size= : Image size (e.g., 1024x1024)}
                            {--model= : AI model to use}
                            {--quality= : Image quality (standard, hd)}
                            {--style= : Image style (vivid, natural)}
                            {--download : Download the image locally}';

    /**
     * The console command description.
     */
    protected $description = 'Generate an AI image from a text prompt';

    /**
     * Execute the console command.
     */
    public function handle(AIImageGenerator $generator): int
    {
        $prompt = $this->argument('prompt');
        
        // Prepare options
        $options = [];
        
        if ($this->option('size')) {
            $options['size'] = $this->option('size');
        }
        
        if ($this->option('model')) {
            $options['model'] = $this->option('model');
        }
        
        if ($this->option('quality')) {
            $options['quality'] = $this->option('quality');
        }
        
        if ($this->option('style')) {
            $options['style'] = $this->option('style');
        }

        $this->info('Generating image...');
        $this->newLine();

        try {
            // Set provider if specified
            if ($this->option('provider')) {
                $generator->provider($this->option('provider'));
            }

            $generation = $generator->generate($prompt, $options);

            $this->info('✅ Image generated successfully!');
            $this->newLine();

            // Display generation details
            $this->table(
                ['Property', 'Value'],
                [
                    ['ID', $generation->id],
                    ['Provider', $generation->provider],
                    ['Model', $generation->model],
                    ['Status', $generation->status],
                    ['Original URL', $generation->original_url],
                    ['Local Path', $generation->file_path ?: 'Not downloaded'],
                    ['Created', $generation->created_at],
                ]
            );

            if ($generation->original_url) {
                $this->info("🔗 Image URL: {$generation->original_url}");
            }

            if ($generation->file_path) {
                $this->info("📁 Local file: {$generation->file_path}");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to generate image: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
