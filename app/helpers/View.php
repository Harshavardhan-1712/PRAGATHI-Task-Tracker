<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Class View
 * Clean template engine supporting layouts, partials, CSRF helpers, and JSON responses.
 */
class View
{
    private static string $viewsPath = __DIR__ . '/../../views';

    /**
     * Render a view file within an optional layout.
     */
    public static function render(string $view, array $data = [], ?string $layout = 'main'): void
    {
        $viewFile = self::$viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "View [{$view}] not found at {$viewFile}";
            return;
        }

        // Extract variables to view scope
        extract($data, EXTR_SKIP);

        // Capture view content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // If no layout requested, output view directly
        if ($layout === null) {
            echo $content;
            return;
        }

        // Render layout
        $layoutFile = self::$viewsPath . '/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            return;
        }

        // Layout variables
        $pageTitle = $data['pageTitle'] ?? 'PRAGATHI — Turn Intentions Into Accomplishments';
        $activeNav = $data['activeNav'] ?? '';
        $currentUser = Session::user();
        $unreadNotificationCount = $data['unreadNotificationCount'] ?? 0;

        include $layoutFile;
    }

    /**
     * Return a JSON response.
     */
    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Redirect to another URL path.
     */
    public static function redirect(string $path): void
    {
        if (!headers_sent()) {
            header("Location: {$path}");
        }
        if (PHP_SAPI !== 'cli') {
            exit;
        }
    }

    /**
     * Escape output for HTML context.
     */
    public static function e(?string $value): string
    {
        return Sanitizer::escape($value);
    }

    /**
     * Render hidden CSRF token input field for forms.
     */
    public static function csrfField(): string
    {
        $token = Session::getCsrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
    }

    /**
     * Get current CSRF token string.
     */
    public static function csrfToken(): string
    {
        return Session::getCsrfToken();
    }
}
