<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI driver that will be used.
    |
    */

    'default' => env('AI_DRIVER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Here you can configure the settings for each AI provider.
    |
    */

    'providers' => [

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
            'temperature' => (float) env('OPENAI_TEMPERATURE', 0.7),
        ],

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
            'temperature' => (float) env('GEMINI_TEMPERATURE', 0.7),
        ],

        'claude' => [
            'api_key' => env('CLAUDE_API_KEY'),
            'model' => env('CLAUDE_MODEL', 'claude-3-sonnet-20240229'),
            'temperature' => (float) env('CLAUDE_TEMPERATURE', 0.7),
            'max_tokens' => (int) env('CLAUDE_MAX_TOKENS', 1024),
        ],

        'mistral' => [
            'api_key' => env('MISTRAL_API_KEY'),
            'model' => env('MISTRAL_MODEL', 'mistral-tiny'),
            'temperature' => (float) env('MISTRAL_TEMPERATURE', 0.7),
        ],

        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'gpt-oss:20b-cloud'),
            'temperature' => (float) env('OLLAMA_TEMPERATURE', 0.7),
        ],
    ],
];
