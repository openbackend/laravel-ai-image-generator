# Laravel AI Image Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/openbackend/laravel-ai-image-generator.svg?style=flat-square)](https://packagist.org/packages/openbackend/laravel-ai-image-generator)
[![Total Downloads](https://img.shields.io/packagist/dt/openbackend/laravel-ai-image-generator.svg?style=flat-square)](https://packagist.org/packages/openbackend/laravel-ai-image-generator)
[![PHP Version Require](https://img.shields.io/packagist/php-v/openbackend/laravel-ai-image-generator?style=flat-square)](https://packagist.org/packages/openbackend/laravel-ai-image-generator)
[![License](https://img.shields.io/packagist/l/openbackend/laravel-ai-image-generator?style=flat-square)](https://packagist.org/packages/openbackend/laravel-ai-image-generator)

An advanced Laravel package for AI-powered image generation supporting multiple providers including OpenAI DALL-E, Stability AI, and Midjourney. Generate stunning images from text prompts with comprehensive features like automatic storage, thumbnails, rate limiting, and detailed analytics.

## Features

- 🎨 **Multiple AI Providers**: OpenAI DALL-E, Stability AI, Midjourney support
- 🔄 **Async Generation**: Support for background image generation
- 💾 **Automatic Storage**: Auto-download and store generated images
- 🖼️ **Thumbnail Generation**: Automatic thumbnail creation in multiple sizes
- 📊 **Analytics & Tracking**: Comprehensive usage statistics and generation history
- 🛡️ **Rate Limiting**: Built-in rate limiting to prevent API abuse
- 🔒 **Content Filtering**: Safety checks for inappropriate content
- ⚡ **Caching**: Intelligent caching for improved performance
- 🎛️ **Artisan Commands**: CLI tools for easy management
- 🧪 **Comprehensive Testing**: Full test suite included
- 📝 **Detailed Logging**: Complete request/response logging
- 🎯 **Facade Support**: Easy-to-use Laravel facades

## Requirements

- PHP 8.1 or higher
- Laravel 9.0, 10.0, or 11.0
- GuzzleHttp 7.5+
- Intervention Image 2.7+ or 3.0+ (optional, for thumbnails)

## Installation

1. Install the package via Composer:

```bash
composer require openbackend/laravel-ai-image-generator
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --provider="OpenBackend\AiImageGenerator\AiImageGeneratorServiceProvider" --tag="ai-image-generator-config"
```

3. Publish and run the migrations:

```bash
php artisan vendor:publish --provider="OpenBackend\AiImageGenerator\AiImageGeneratorServiceProvider" --tag="ai-image-generator-migrations"
php artisan migrate
```

4. Add your API keys to your `.env` file:

```env
# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_IMAGE_MODEL=dall-e-3

# Stability AI Configuration  
STABILITY_API_KEY=your_stability_api_key_here

# Midjourney Configuration
MIDJOURNEY_API_KEY=your_midjourney_api_key_here
MIDJOURNEY_API_URL=your_midjourney_api_url_here
```

## Quick Start

```php
use OpenBackend\AiImageGenerator\Facades\AIImageGenerator;

// Generate an image with default settings
$generation = AIImageGenerator::generate('A beautiful sunset over mountains');

echo $generation->url; // URL to the generated image
echo $generation->file_path; // Local file path (if auto-download enabled)
```

## Advanced Usage

```php
// Generate with custom options
$generation = AIImageGenerator::generate('A futuristic city', [
    'size' => '1792x1024',
    'quality' => 'hd',
    'style' => 'natural',
    'model' => 'dall-e-3'
]);

// Use a specific provider
$generation = AIImageGenerator::provider('stability')
    ->generate('A magical forest', [
        'width' => 1024,
        'height' => 1024,
        'steps' => 50,
        'cfg_scale' => 7
    ]);

// Async generation (queued)
$generation = AIImageGenerator::generateAsync('A space station');
```

## Artisan Commands

```bash
# Generate an image from command line
php artisan ai-image:generate "A beautiful landscape" --provider=openai --size=1024x1024

# List available providers
php artisan ai-image:providers
```

## Configuration

The package provides extensive configuration options in `config/aiimagegenerator.php`:

- Multiple AI provider support
- Storage and file management
- Rate limiting and security
- Logging and monitoring
- Image processing options
- Caching configuration

## Testing

```bash
composer test
composer test-coverage
composer analyse
```

## Credits

- [OpenBackend Organization](https://github.com/openbackend)
- [Rudra Ramesh](https://github.com/rudraramesh) - Lead Developer
- [All Contributors](../../contributors)

## About OpenBackend

OpenBackend is an open-source organization focused on creating high-quality backend solutions and packages for modern web development. We specialize in Laravel packages, API development tools, and backend infrastructure solutions.

Visit us at [openbackend.dev](https://openbackend.dev) to explore more of our projects.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Support

- 📧 Email: contact@openbackend.dev
- 🐛 Issues: [GitHub Issues](https://github.com/openbackend/laravel-ai-image-generator/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/openbackend/laravel-ai-image-generator/discussions)
- 🌐 Website: [openbackend.dev](https://openbackend.dev)
