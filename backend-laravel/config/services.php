<?php


/**
 * Capa Laravel: services.
 * Comentaris: agents/backend/AgentLaravel.md
 */

return [
    'welcome_email_enabled' => filter_var(
        env('WELCOME_EMAIL_ENABLED', true),
        FILTER_VALIDATE_BOOLEAN
    ),

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],
    'google_books' => [
        'api_key' => env('GOOGLE_BOOKS_API_KEY'),
        'base_url' => env('GOOGLE_BOOKS_API_URL', 'https://www.googleapis.com/books/v1'),
    ],
    'wger' => [
        'api_token' => env('WGER_API_TOKEN'),
        'base_url' => env('WGER_API_URL', 'https://wger.de/api/v2'),
    ],
    'youtube' => [
        'api_key' => env('YOUTUBE_DATA_API_KEY'),
        'base_url' => env('YOUTUBE_DATA_API_URL', 'https://www.googleapis.com/youtube/v3'),
    ],
    'api_ninjas' => [
        'api_key' => env('API_NINJAS_API_KEY'),
        'base_url' => env('API_NINJAS_API_URL', 'https://api.api-ninjas.com/v1'),
    ],
    'openfoodfacts' => [
        'base_url'   => env('OPENFOODFACTS_API_URL',    'https://world.openfoodfacts.org'),
        'search_url' => env('OPENFOODFACTS_SEARCH_URL', 'https://search.openfoodfacts.org'),
    ],
    'openweather' => [
        'api_key' => env('OPENWEATHER_API_KEY'),
        'base_url' => env('OPENWEATHER_API_URL', 'https://api.openweathermap.org/data/2.5'),
    ],
];
