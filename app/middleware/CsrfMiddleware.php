<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Session;
use App\Helpers\View;

class CsrfMiddleware
{
    public static function handle(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Only validate state-changing methods
        if (in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $token = $_POST['_csrf_token'] ?? null;

            // Check header if not in POST body (for AJAX / Fetch API requests)
            if (empty($token)) {
                $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            }

            // Check php input json if applicable
            if (empty($token)) {
                $rawInput = file_get_contents('php://input');
                if (!empty($rawInput)) {
                    $json = json_decode($rawInput, true);
                    if (is_array($json) && !empty($json['_csrf_token'])) {
                        $token = $json['_csrf_token'];
                    }
                }
            }

            if (!Session::validateCsrfToken($token)) {
                $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                    || (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'))
                    || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

                if ($isAjax) {
                    View::json(['success' => false, 'error' => 'Security token (CSRF) expired or invalid. Please refresh the page.'], 403);
                    return false;
                }

                http_response_code(403);
                View::render('errors.403', [
                    'pageTitle' => '403 Forbidden — Security Token Mismatch',
                    'message' => 'CSRF verification failed. Please return to the previous page, refresh, and try again.'
                ], 'main');
                return false;
            }
        }

        return true;
    }
}
