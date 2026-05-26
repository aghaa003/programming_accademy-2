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

    /*
    |--------------------------------------------------------------------------
    | Allow frontend dev hosts by pattern
    |--------------------------------------------------------------------------
    | Add a permissive pattern that matches localhost and 127.0.0.1 with any
    | port (useful for Vite / dev servers). This keeps `allowed_origins`
    | explicit while allowing common dev ports without editing this file
    | every time. In production, prefer explicit origins.
    */
    'allowed_origins_patterns' => [
        '/^https?:\/\/(localhost|127\.0\.0\.1)(:\\d+)?$/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed headers
    |--------------------------------------------------------------------------
    | Accept all request headers from the frontend to avoid CORS failures
    | during development. You can make this more restrictive in production.
    */
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
    /*      'allowed_headers' => ['Content-Type', 'X-Requested-With', 'X-XSRF-TOKEN', 'Accept', 'Authorization'],
 */
];
