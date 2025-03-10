<?php


return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'botman', 'botman/*'], // ✅ Add BotMan paths

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://127.0.0.1:5501', 'https://www.maksoft.in', 'https://sourceopen.in'], // ✅ Add your domains

    'allowed_headers' => ['X-Requested-With', 'Content-Type', 'X-CSRF-TOKEN', '*'], // ✅ Allow CSRF Header

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
