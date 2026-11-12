<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Class Session
 * Manages secure session lifecycle, flash messages, CSRF tokens, and authentication state.
 */
class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $appConfig = require __DIR__ . '/../config/app.php';
        $sessionConfig = $appConfig['session'] ?? [];

        // Security-hardened cookie configuration
        if (!headers_sent()) {
            session_set_cookie_params([
                'lifetime' => $sessionConfig['lifetime'] ?? 7200,
                'path' => '/',
                'domain' => '',
                'secure' => (bool) ($sessionConfig['secure'] ?? false),
                'httponly' => true,
                'samesite' => $sessionConfig['samesite'] ?? 'Lax',
            ]);

            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');

            session_start();
        } else if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        self::$started = true;

        // Auto-generate CSRF token if not set
        if (empty($_SESSION['_csrf_token'])) {
            self::generateCsrfToken();
        }
    }

    public static function regenerate(): void
    {
        self::start();
        if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];

        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
        self::$started = false;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    // --- FLASH MESSAGES ---

    public static function setFlash(string $type, string $message): void
    {
        self::start();
        $_SESSION['_flashes'][$type][] = $message;
    }

    public static function getFlash(string $type): array
    {
        self::start();
        $messages = $_SESSION['_flashes'][$type] ?? [];
        unset($_SESSION['_flashes'][$type]);
        return $messages;
    }

    public static function hasFlash(string $type): bool
    {
        self::start();
        return !empty($_SESSION['_flashes'][$type]);
    }

    public static function getAllFlashes(): array
    {
        self::start();
        $flashes = $_SESSION['_flashes'] ?? [];
        unset($_SESSION['_flashes']);
        return $flashes;
    }

    // --- CSRF PROTECTION ---

    public static function generateCsrfToken(): string
    {
        self::start();
        $token = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token'] = $token;
        return $token;
    }

    public static function getCsrfToken(): string
    {
        self::start();
        if (empty($_SESSION['_csrf_token'])) {
            return self::generateCsrfToken();
        }
        return $_SESSION['_csrf_token'];
    }

    public static function validateCsrfToken(?string $token): bool
    {
        self::start();
        $sessionToken = $_SESSION['_csrf_token'] ?? '';
        if (empty($sessionToken) || empty($token)) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }

    // --- AUTHENTICATION STATE ---

    public static function loginUser(array $user): void
    {
        self::regenerate();
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';
        $_SESSION['user_theme'] = $user['theme_preference'] ?? 'light';
        $_SESSION['logged_in_at'] = time();
    }

    public static function logoutUser(): void
    {
        self::destroy();
    }

    public static function isLoggedIn(): bool
    {
        self::start();
        return !empty($_SESSION['user_id']);
    }

    public static function userId(): int
    {
        self::start();
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    public static function user(): ?array
    {
        self::start();
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'user',
            'theme' => $_SESSION['user_theme'] ?? 'light',
        ];
    }
}
