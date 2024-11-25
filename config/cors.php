<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS Paths
    |--------------------------------------------------------------------------
    |
    | Define the paths that should be accessible via CORS. Typically, you'd want
    | this to be set to your API paths.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | Specify which HTTP methods are allowed for cross-origin requests.
    | You can set it to '*' to allow all methods or specify a list of methods.
    |
    */

    'allowed_methods' => ['*'], // Allow all methods or specify as needed

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Define which origins are allowed to make cross-origin requests.
    | You should list the front-end application's URL here.
    |
    */

    'allowed_origins' => [
        'http://localhost:5174', // New URL for your frontend
        'http://localhost:5173', // Previous URL if still needed
        'http://localhost:8080',  // New URL to be added
    ],

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Define which headers are allowed in the cross-origin request.
    | You can set it to '*' to allow all headers or specify a list.
    |
    */

    'allowed_headers' => ['*'], // Permetti tutti gli header

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Define which headers should be exposed to the browser.
    |
    */

    'exposed_headers' => false,

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | Define the maximum time (in seconds) that the pre-flight request can be cached.
    |
    */

    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Set to true if you want to allow credentials (cookies) to be sent.
    |
    */

    'supports_credentials' => true, // Permette l'invio dei cookie
];