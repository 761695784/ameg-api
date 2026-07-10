<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Domaine du site Next.js autorisé à appeler l'API avec des cookies.
    // En local, ajoute aussi http://localhost:3000 pour tes tests.
    'allowed_origins' => [
        env('FRONTEND_URL', 'https://ameginternational.com'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // OBLIGATOIRE pour Sanctum SPA (cookies) : doit être à true, jamais '*' dans allowed_origins ci-dessus.
    'supports_credentials' => true,

];
