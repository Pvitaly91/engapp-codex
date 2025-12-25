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

    'chatgpt' => [
        'key' => env('CHAT_GPT_API_KEY'),
        'language' => env('CHAT_GPT_LANGUAGE', 'uk'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY', env('CHAT_GPT_API_KEY')),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout' => env('OPENAI_TIMEOUT', 60),
        'max_retries' => env('OPENAI_MAX_RETRIES', 3),
    ],

    'gemini' => [
        'key'   => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash-exp'),
        'timeout' => env('GEMINI_TIMEOUT', 60),
        'max_retries' => env('GEMINI_MAX_RETRIES', 3),
    ],

];
