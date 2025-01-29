# Laravel AI Image Generator

A Laravel package for AI-powered image generation using OpenAI DALL·E and Stable Diffusion.

## Installation

1. Add the package to your Laravel project:
   ```bash
   composer require rbb/ai-image-generator

2. Publish Configuration
   ```bash
   php artisan vendor:publish --provider="Rbb\LaravelAiImageGenerator\LaravelAiImageGeneratorServiceProvider"


3. Register the Service Provider  in the config/app.php of the Laravel project
   ```bash
    'providers' => [
        Rbb\LaravelAiImageGenerator\LaravelAiImageGeneratorServiceProvider::class,
        ],

4. Access the AI Image Generator in your app

   ```bash
    use Rbb\LaravelAiImageGenerator\Facades\AIImageGenerator;
    // Generate an image from a prompt
    $imageUrl = AIImageGenerator::generate('A futuristic cityscape at sunset.');
    echo $imageUrl;
