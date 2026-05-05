<?php

$frontend = env('FRONTEND_URL', 'http://localhost:3000');

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'auth/*', 'chat/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 86400,
    'supports_credentials' => true,
];
