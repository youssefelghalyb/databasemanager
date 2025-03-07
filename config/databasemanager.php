<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Manager Configuration
    |--------------------------------------------------------------------------
    */

    // The connection name used by the Database Manager package
    'connection' => env('DB_MANAGER_CONNECTION', 'database_manager'),

    // Default settings for new connections
    'defaults' => [
        'driver' => 'mysql',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],

    // Routes configuration
    'routes' => [
        'prefix' => 'database-designer',
        'middleware' => ['web'], // You can add auth middleware here
    ],
];