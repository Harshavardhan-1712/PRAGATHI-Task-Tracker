<?php

declare(strict_types=1);

use App\Helpers\Env;

return [
    'default' => Env::get('DB_CONNECTION', 'mysql'),
    'fallback_sqlite' => (bool) Env::get('DB_FALLBACK_SQLITE', true),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => Env::get('DB_HOST', '127.0.0.1'),
            'port' => (int) Env::get('DB_PORT', 3306),
            'database' => Env::get('DB_DATABASE', 'pragathi_db'),
            'username' => Env::get('DB_USERNAME', 'root'),
            'password' => Env::get('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'options' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../../' . ltrim(Env::get('SQLITE_PATH', 'storage/pragathi.sqlite'), '/\\'),
            'options' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],
    ],
];
