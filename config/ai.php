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

    'default' => env('LARAVEL_AURA_AI_DRIVER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Global Failover Chain
    |--------------------------------------------------------------------------
    |
    | Define a global chain of providers to try if the default one fails.
    | You can also define this per-provider.
    |
    */
    'fallbacks' => ['gemini', 'claude'],

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
            'api_key' => env('LARAVEL_AURA_OPENAI_API_KEY'),
            'model' => env('LARAVEL_AURA_OPENAI_MODEL', 'gpt-3.5-turbo'),
            'temperature' => (float) env('LARAVEL_AURA_OPENAI_TEMPERATURE', 0.7),
        ],

        'gemini' => [
            'api_key' => env('LARAVEL_AURA_GEMINI_API_KEY'),
            'model' => env('LARAVEL_AURA_GEMINI_MODEL', 'gemini-1.5-flash'),
            'temperature' => (float) env('LARAVEL_AURA_GEMINI_TEMPERATURE', 0.7),
        ],

        'claude' => [
            'api_key' => env('LARAVEL_AURA_CLAUDE_API_KEY'),
            'model' => env('LARAVEL_AURA_CLAUDE_MODEL', 'claude-3-sonnet-20240229'),
            'temperature' => (float) env('LARAVEL_AURA_CLAUDE_TEMPERATURE', 0.7),
            'max_tokens' => (int) env('LARAVEL_AURA_CLAUDE_MAX_TOKENS', 1024),
        ],

        'mistral' => [
            'api_key' => env('LARAVEL_AURA_MISTRAL_API_KEY'),
            'model' => env('LARAVEL_AURA_MISTRAL_MODEL', 'mistral-tiny'),
            'temperature' => (float) env('LARAVEL_AURA_MISTRAL_TEMPERATURE', 0.7),
        ],

        'ollama' => [
            'base_url' => env('LARAVEL_AURA_OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model' => env('LARAVEL_AURA_OLLAMA_MODEL', 'gpt-oss:20b-cloud'),
            'temperature' => (float) env('LARAVEL_AURA_OLLAMA_TEMPERATURE', 0.7),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PII Masking
    |--------------------------------------------------------------------------
    |
    | This option controls the PII masking settings.
    |
    */

    'pii_masking' => [
        /*
        |--------------------------------------------------------------------------
        | Enable PII Masking
        |--------------------------------------------------------------------------
        |
        | When enabled, the package will automatically detect and mask sensitive
        | information (PII) before sending it to the AI provider.
        |
        */
        'enabled' => env('LARAVEL_AURA_AI_PII_MASKING_ENABLED', true),

        /*
        |--------------------------------------------------------------------------
        | Strict Mode (Zero Liability)
        |--------------------------------------------------------------------------
        |
        | In strict mode, if PII is detected and cannot be safely masked or 
        | if unmasking is disabled, the request will be aborted or 
        | heavily redacted to ensure zero liability.
        |
        */
        'strict' => env('LARAVEL_AURA_AI_PII_STRICT_MODE', false),

        'unmasking' => [
            'enabled' => env('LARAVEL_AURA_AI_PII_UNMASKING_ENABLED', true),
        ],
        /*
        |--------------------------------------------------------------------------
        | Custom PII Patterns
        |--------------------------------------------------------------------------
        |
        | You can define custom regex patterns to be masked.
        | The key will be used as the mask type (e.g. [MASKED_MY_TYPE_ID]).
        |
        */
        'patterns' => [
            'email' => '/\b[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,63}\b/i',
            'phone' => '/\b(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}\b/',
            'ssn' => '/\b\d{3}-\d{2}-\d{4}\b/',
            'ipv4' => '/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/',
            'ipv6' => '/\b(?:(?:[0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|(?:[0-9a-fA-F]{1,4}:){1,7}:|(?:[0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|(?:[0-9a-fA-F]{1,4}:){1,5}(?::[0-9a-fA-F]{1,4}){1,2}|(?:[0-9a-fA-F]{1,4}:){1,4}(?::[0-9a-fA-F]{1,4}){1,3}|(?:[0-9a-fA-F]{1,4}:){1,3}(?::[0-9a-fA-F]{1,4}){1,4}|(?:[0-9a-fA-F]{1,4}:){1,2}(?::[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:(?::[0-9a-fA-F]{1,4}){1,6}|:(?:(?::[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(?::[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(?:ffff(?::0{1,4}){0,1}:){0,1}(?:(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])|(?:[0-9a-fA-F]{1,4}:){1,4}:(?:(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9]))\b/',
            'mac_address' => '/\b(?:[0-9A-Fa-f]{2}[:-]){5}(?:[0-9A-Fa-f]{2})\b/',
            'iban' => '/\b[A-Z]{2}[0-9]{2}[A-Z0-9]{11,30}\b/',
            'jwt' => '/\beyJ[A-Za-z0-9\-_=]+\.[A-Za-z0-9\-_=]+\.[A-Za-z0-9\-_.\+\/=]+\b/',
            'aws_access_key' => '/\bAKIA[0-9A-Z]{16}\b/',
            'aws_secret_key' => '/\b[a-zA-Z0-9\/+]{40}\b/',
            'api_key' => '/\b(?:sk|pk|ak|mk)_[a-zA-Z0-9]{32,}\b/',
            'passport_number' => '/\b[A-Z]{1,2}[0-9]{6,9}\b/',
            'credit_card' => '/\b(?:\d{4}[- ]?\d{4}[- ]?\d{4}[- ]?\d{4}|\d{4}[- ]?\d{6}[- ]?\d{5})\b/',
            'private_key' => '/-----BEGIN (?:RSA |EC |DSA |OPENSSH )?PRIVATE KEY-----[\s\S]*?-----END (?:RSA |EC |DSA |OPENSSH )?PRIVATE KEY-----/s',
        ],

        /*
        |--------------------------------------------------------------------------
        | Custom Replacements
        |--------------------------------------------------------------------------
        |
        | If unmasking is disabled, you can define custom replacement strings
        | for specific PII types.
        |
        */
        'replacements' => [
            // 'email' => '[REDACTED EMAIL]',
        ],
    ],
];
