<?php


/**
 * Capa Laravel: cache.
 * Comentaris: agents/backend/AgentLaravel.md
 */

return [
    'default' => env('CACHE_DRIVER', 'redis'),
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],
    ],
];
