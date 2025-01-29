# Laravel AI Image Generator

A Laravel package for AI-powered image generation using OpenAI DALL·E and Stable Diffusion.

## Installation

1. Add the package to your Laravel project:
   ```bash
   composer require rudraramesh/ai-image-generator

2. Publish Configuration
   ```bash
   php artisan vendor:publish --provider="Rbb\AiImageGenerator\AiImageGeneratorServiceProvider"


3. Register the Service Provider  in the config/app.php of the Laravel project
   ```bash
    'providers' => [
        Rbb\AiImageGenerator\AiImageGeneratorServiceProvider::class,
        ],

4. Access the AI Image Generator in your app

   ```bash
    use Rbb\AiImageGenerator\Facades\AIImageGenerator;
    // Generate an image from a prompt
    $imageUrl = AIImageGenerator::generate('A futuristic cityscape at sunset.');
    echo $imageUrl;
