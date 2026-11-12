<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Session;
use App\Helpers\View;

class AuthMiddleware
{
    public static function handle(): bool
    {
        if (!Session::isLoggedIn()) {
            // Check if it's an AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                View::json(['success' => false, 'error' => 'Authentication required.'], 401);
                return false;
            }

            Session::setFlash('warning', 'Please sign in to access your workspace.');
            View::redirect('/login');
            return false;
        }

        return true;
    }
}
