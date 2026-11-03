<?php

return [
    'admin_username' => env('ADMIN_USERNAME', 'admin'),
    'public_url' => env('PUBLIC_URL', env('APP_URL', 'http://localhost')),
    'web_root' => env('MENU_PUBLIC_ROOT') ?: public_path(),
];
