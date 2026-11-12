<?php

declare(strict_types=1);

use App\Helpers\Env;

return [
    'name' => Env::get('APP_NAME', 'PRAGATHI'),
    'tagline' => Env::get('APP_TAGLINE', 'Turn Intentions Into Accomplishments'),
    'env' => Env::get('APP_ENV', 'development'),
    'debug' => Env::get('APP_DEBUG', true),
    'url' => Env::get('APP_URL', 'http://localhost:8000'),
    'timezone' => Env::get('APP_TIMEZONE', 'Asia/Kolkata'),
    'session' => [
        'lifetime' => (int) Env::get('SESSION_LIFETIME', 7200),
        'secure' => (bool) Env::get('SESSION_SECURE_COOKIE', false),
        'httponly' => true,
        'samesite' => 'Lax',
    ],
];
