<?php

declare(strict_types=1);

/**
 * PRAGATHI — Intelligent Task & Productivity Management Platform
 * Front Controller & Application Entry Point
 */

// 1. Setup PSR-4 style lightweight autoloader
spl_autoload_register(function (string $class) {
    $prefixes = [
        'App\\Controllers\\' => __DIR__ . '/../app/controllers/',
        'App\\Models\\'      => __DIR__ . '/../app/models/',
        'App\\Services\\'    => __DIR__ . '/../app/services/',
        'App\\Middleware\\'  => __DIR__ . '/../app/middleware/',
        'App\\Helpers\\'     => __DIR__ . '/../app/helpers/',
        'App\\Routes\\'      => __DIR__ . '/../routes/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

use App\Helpers\Env;
use App\Helpers\Session;
use App\Helpers\View;

// 2. Load Environment Variables
Env::load(__DIR__ . '/../.env');

// 3. Configure Timezone
$appConfig = require __DIR__ . '/../app/config/app.php';
date_default_timezone_set($appConfig['timezone'] ?? 'Asia/Kolkata');

// 4. Initialize Hardened Session
Session::start();

// 5. Global Exception & Error Handler
set_exception_handler(function (\Throwable $e) use ($appConfig) {
    http_response_code(500);

    // Log to file
    $logFile = __DIR__ . '/../storage/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] " . get_class($e) . ": " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND);

    if (!empty($appConfig['debug'])) {
        View::render('errors.500', [
            'pageTitle' => '500 Server Error — PRAGATHI',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ], 'main');
    } else {
        View::render('errors.500', [
            'pageTitle' => '500 Server Error — PRAGATHI',
            'error' => 'An unexpected server error occurred. Our engineering team has logged this incident.',
            'file' => '',
            'line' => 0,
            'trace' => '',
        ], 'main');
    }
});

// 6. Load & Dispatch Web Routes
$router = require __DIR__ . '/../routes/web.php';

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($requestMethod, $requestUri);
