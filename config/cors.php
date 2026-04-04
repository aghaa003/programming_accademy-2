<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        'http://localhost',
        'http://localhost:8000',
        'http://localhost:3000',   // Create React App
        'http://localhost:5173',   // Vite (recommended)
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'X-XSRF-TOKEN', 'Accept', 'Authorization'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
