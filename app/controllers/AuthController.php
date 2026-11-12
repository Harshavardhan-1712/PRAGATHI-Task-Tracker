<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Session;
use App\Helpers\Validator;
use App\Helpers\View;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;

class AuthController
{
    public function showLogin(): void
    {
        View::render('auth.login', [
            'pageTitle' => 'Sign In — PRAGATHI',
        ], 'auth');
    }

    public function login(): void
    {
        // Rate-limiting / brute-force throttling check
        $attempts = (int) Session::get('login_attempts', 0);
        $lastAttempt = (int) Session::get('last_login_attempt', 0);

        if ($attempts >= 5 && (time() - $lastAttempt) < 60) {
            $waitSeconds = 60 - (time() - $lastAttempt);
            Session::setFlash('error', "Too many failed attempts. Please wait {$waitSeconds} seconds before trying again.");
            View::redirect('/login');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
        ], [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError() ?? 'Please enter your email and password.');
            View::redirect('/login');
            return;
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::set('login_attempts', $attempts + 1);
            Session::set('last_login_attempt', time());

            // Generic error message to prevent account enumeration
            Session::setFlash('error', 'Invalid email or password. Please try again.');
            View::redirect('/login');
            return;
        }

        // Login successful: reset throttling counters
        Session::remove('login_attempts');
        Session::remove('last_login_attempt');

        // Log user in with session regeneration
        Session::loginUser($user);
        Session::setFlash('success', "Welcome back, {$user['name']}!");

        View::redirect('/dashboard');
    }

    public function showRegister(): void
    {
        View::render('auth.register', [
            'pageTitle' => 'Create Account — PRAGATHI',
        ], 'auth');
    }

    public function register(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirmation'] ?? '';

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirm,
        ], [
            'name' => ['required', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'max:100'],
            'password_confirmation' => ['required', 'matches:password'],
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError() ?? 'Registration validation failed.');
            View::redirect('/register');
            return;
        }

        $userId = User::create($name, $email, $password);
        $user = User::findById($userId);

        if ($user) {
            Session::loginUser($user);

            // Log activity and welcome notification
            ActivityLog::log($userId, null, 'created', 'Created PRAGATHI account');
            Notification::create(
                $userId,
                'Welcome to PRAGATHI!',
                'Your productivity workspace is ready. Turn intentions into accomplishments!',
                'success',
                '/dashboard'
            );

            Session::setFlash('success', 'Account created successfully! Welcome to PRAGATHI.');
            View::redirect('/dashboard');
            return;
        }

        Session::setFlash('error', 'Could not create account. Please try again.');
        View::redirect('/register');
    }

    public function logout(): void
    {
        Session::logoutUser();
        Session::setFlash('info', 'You have been securely logged out.');
        View::redirect('/login');
    }
}
