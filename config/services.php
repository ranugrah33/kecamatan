<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
        'max_output_tokens' => max(256, min(8192, (int) env('GEMINI_MAX_OUTPUT_TOKENS', 2048))),
        'cooldown_seconds' => max(5, min(600, (int) env('GEMINI_COOLDOWN_SECONDS', 45))),
        'cooldown_max_seconds' => max(30, min(600, (int) env('GEMINI_COOLDOWN_MAX_SECONDS', 180))),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'qwen/qwen3.8-27b'),
        // Conservative default matching Groq on_demand OTPM account limit (1000 OTPM)
        'max_tokens' => max(256, min(4096, (int) env('GROQ_MAX_TOKENS', 1024))),
    ],

    'tavily' => [
        'api_key' => env('TAVILY_API_KEY'),
        'endpoint' => env('TAVILY_ENDPOINT', 'https://api.tavily.com/search'),
        'timeout' => (int) env('TAVILY_TIMEOUT', 8),
        'search_depth' => env('TAVILY_SEARCH_DEPTH', 'basic'),
        'max_results' => (int) env('TAVILY_MAX_RESULTS', 5),
        'max_content_chars' => (int) env('TAVILY_MAX_CONTENT_CHARS', 500),
        'max_context_chars' => (int) env('TAVILY_MAX_CONTEXT_CHARS', 3500),
        'cache_ttl_minutes' => (int) env('TAVILY_CACHE_TTL_MINUTES', 60),
        'per_minute_limit' => (int) env('TAVILY_PER_MINUTE_LIMIT', 15),
        'daily_limit' => (int) env('TAVILY_DAILY_LIMIT', 80),
        'enabled' => filter_var(env('TAVILY_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    ],

    'ai' => [
        'primary' => env('AI_PRIMARY', 'gemini'),
        'fallback' => env('AI_FALLBACK', 'groq'),
        'timeout' => (int) env('AI_TIMEOUT', 20),
        'max_retries' => (int) env('AI_MAX_RETRIES', 1),
        'rag' => [
            'max_results' => (int) env('AI_RAG_MAX_RESULTS', 4),
            'min_score' => (float) env('AI_RAG_MIN_SCORE', 3.5),
            'max_context_chars' => (int) env('AI_RAG_MAX_CONTEXT_CHARS', 6000),
        ],
    ],

];

