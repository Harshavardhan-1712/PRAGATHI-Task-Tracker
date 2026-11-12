<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Session;
use App\Helpers\View;

class GuestMiddleware
{
    public static function handle(): bool
    {
        if (Session::isLoggedIn()) {
            View::redirect('/dashboard');
            return false;
        }

        return true;
    }
}
