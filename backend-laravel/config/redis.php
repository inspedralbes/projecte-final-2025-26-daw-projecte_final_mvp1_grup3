<?php

return [
    'client' => env('REDIS_CLIENT', 'predis'),
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
        // Timeout de lectura per evitar talls en operacions bloquejants
        'read_timeout' => (float) env('REDIS_READ_TIMEOUT', 60),
        // Predis usa read_write_timeout; phpredis usa read_timeout
        'read_write_timeout' => (float) env('REDIS_READ_WRITE_TIMEOUT', 60),
    ],
    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
];
