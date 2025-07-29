<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Image Provider
    |--------------------------------------------------------------------------
    |
    | This option defines the default AI image generation provider that will
    | be used by the package. You can change this to any of the supported
    | providers: openai, stability, midjourney, custom
    |
    */
    'default' => env('AI_IMAGE_DEFAULT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | AI Image Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the AI image generation providers for your
    | application. Out of the box, this package supports multiple providers.
    |
    */
    'providers' => [
        'openai' => [
            'driver' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_IMAGE_MODEL', 'dall-e-3'),
            'max_retries' => 3,
            'timeout' => 120,
            'default_options' => [
                'size' => '1024x1024',
                'quality' => 'standard', // standard, hd
                'style' => 'vivid', // vivid, natural
                'response_format' => 'url', // url, b64_json
            ],
        ],

        'stability' => [
            'driver' => 'stability',
            'api_key' => env('STABILITY_API_KEY'),
            'api_url' => env('STABILITY_API_URL', 'https://api.stability.ai/v1'),
            'engine' => env('STABILITY_ENGINE', 'stable-diffusion-xl-1024-v1-0'),
            'max_retries' => 3,
            'timeout' => 120,
            'default_options' => [
                'width' => 1024,
                'height' => 1024,
                'cfg_scale' => 7,
                'steps' => 30,
                'samples' => 1,
            ],
        ],

        'midjourney' => [
            'driver' => 'midjourney',
            'api_key' => env('MIDJOURNEY_API_KEY'),
            'api_url' => env('MIDJOURNEY_API_URL'),
            'max_retries' => 3,
            'timeout' => 300, // Midjourney typically takes longer
            'default_options' => [
                'aspect_ratio' => '1:1',
                'version' => '6',
                'quality' => 1,
                'stylize' => 100,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how generated images should be stored and managed.
    |
    */
    'storage' => [
        'disk' => env('AI_IMAGE_STORAGE_DISK', 'public'),
        'path' => env('AI_IMAGE_STORAGE_PATH', 'ai-generated-images'),
        'auto_download' => env('AI_IMAGE_AUTO_DOWNLOAD', true),
        'cleanup_temp' => env('AI_IMAGE_CLEANUP_TEMP', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configure database storage for generated images metadata.
    |
    */
    'database' => [
        'enabled' => env('AI_IMAGE_DB_ENABLED', true),
        'table' => env('AI_IMAGE_DB_TABLE', 'ai_generated_images'),
        'store_prompts' => env('AI_IMAGE_STORE_PROMPTS', true),
        'store_metadata' => env('AI_IMAGE_STORE_METADATA', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for API requests to prevent hitting provider limits.
    |
    */
    'rate_limiting' => [
        'enabled' => env('AI_IMAGE_RATE_LIMITING', true),
        'requests_per_minute' => env('AI_IMAGE_REQUESTS_PER_MINUTE', 5),
        'requests_per_hour' => env('AI_IMAGE_REQUESTS_PER_HOUR', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing
    |--------------------------------------------------------------------------
    |
    | Configure image processing options like resizing, format conversion, etc.
    |
    */
    'image_processing' => [
        'enabled' => env('AI_IMAGE_PROCESSING_ENABLED', true),
        'auto_optimize' => env('AI_IMAGE_AUTO_OPTIMIZE', true),
        'formats' => ['jpg', 'png', 'webp'],
        'max_file_size' => env('AI_IMAGE_MAX_FILE_SIZE', 10240), // KB
        'thumbnails' => [
            'enabled' => true,
            'sizes' => [
                'small' => [300, 300],
                'medium' => [600, 600],
                'large' => [1200, 1200],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security & Validation
    |--------------------------------------------------------------------------
    |
    | Configure security and validation settings.
    |
    */
    'security' => [
        'content_filter' => env('AI_IMAGE_CONTENT_FILTER', true),
        'prompt_validation' => env('AI_IMAGE_PROMPT_VALIDATION', true),
        'max_prompt_length' => env('AI_IMAGE_MAX_PROMPT_LENGTH', 1000),
        'allowed_file_types' => ['jpg', 'jpeg', 'png', 'webp'],
        'virus_scan' => env('AI_IMAGE_VIRUS_SCAN', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging & Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure logging and monitoring options.
    |
    */
    'logging' => [
        'enabled' => env('AI_IMAGE_LOGGING_ENABLED', true),
        'log_requests' => env('AI_IMAGE_LOG_REQUESTS', true),
        'log_responses' => env('AI_IMAGE_LOG_RESPONSES', false),
        'log_errors' => env('AI_IMAGE_LOG_ERRORS', true),
        'channel' => env('AI_IMAGE_LOG_CHANNEL', 'daily'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for improved performance.
    |
    */
    'cache' => [
        'enabled' => env('AI_IMAGE_CACHE_ENABLED', true),
        'ttl' => env('AI_IMAGE_CACHE_TTL', 3600), // seconds
        'store' => env('AI_IMAGE_CACHE_STORE', 'file'),
        'prefix' => env('AI_IMAGE_CACHE_PREFIX', 'ai_image_'),
    ],
];
