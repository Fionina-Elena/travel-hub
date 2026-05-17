<?php

return [

    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://localhost:11434'),
        'embedding_model' => env('OLLAMA_EMBEDDING_MODEL', 'nomic-embed-text'),
        'llm_model' => env('OLLAMA_LLM_MODEL', 'llama3.1'),
    ],

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

];
