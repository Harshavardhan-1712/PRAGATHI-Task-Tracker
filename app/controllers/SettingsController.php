<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Session;
use App\Helpers\Validator;
use App\Helpers\View;
use App\Models\Category;
use App\Models\Notification;
use App\Models\User;

class SettingsController
{
    public function index(): void
    {
        $userId = Session::userId();
        $user = User::findById($userId);
        $categories = Category::getAllForUser($userId);
        $unreadCount = Notification::getUnreadCount($userId);

        View::render('settings.index', [
            'pageTitle' => 'Account & Workspace Settings — PRAGATHI',
            'activeNav' => 'settings',
            'user' => $user,
            'categories' => $categories,
            'unreadNotificationCount' => $unreadCount,
        ]);
    }

    public function updateProfile(): void
    {
        $userId = Session::userId();
        $name = trim($_POST['name'] ?? '');
        $theme = in_array($_POST['theme_preference'] ?? '', ['light', 'dark'], true) ? $_POST['theme_preference'] : 'light';

        $validator = Validator::make(['name' => $name], [
            'name' => ['required', 'min:2', 'max:100'],
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError() ?? 'Invalid profile input.');
            View::redirect('/settings');
            return;
        }

        User::updateProfile($userId, $name, $theme);

        // Update active session values
        Session::set('user_name', $name);
        Session::set('user_theme', $theme);

        Session::setFlash('success', 'Profile and preferences updated successfully.');
        View::redirect('/settings');
    }

    public function updatePassword(): void
    {
        $userId = Session::userId();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['new_password_confirmation'] ?? '';

        $validator = Validator::make([
            'current_password' => $currentPassword,
            'new_password' => $newPassword,
            'new_password_confirmation' => $confirmPassword,
        ], [
            'current_password' => ['required'],
            'new_password' => ['required', 'min:8', 'max:100'],
            'new_password_confirmation' => ['required', 'matches:new_password'],
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError() ?? 'Password update validation failed.');
            View::redirect('/settings');
            return;
        }

        $user = User::findByEmail(Session::get('user_email', ''));
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            Session::setFlash('error', 'Your current password was entered incorrectly.');
            View::redirect('/settings');
            return;
        }

        User::updatePassword($userId, $newPassword);
        Session::setFlash('success', 'Password updated successfully.');
        View::redirect('/settings');
    }

    public function createCategory(): void
    {
        $userId = Session::userId();
        $name = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#6366f1');
        $icon = trim($_POST['icon'] ?? 'bi-tag');

        $validator = Validator::make(['name' => $name], [
            'name' => ['required', 'min:2', 'max:50'],
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError() ?? 'Category name is required.');
            View::redirect('/settings');
            return;
        }

        Category::create($userId, $name, $color, $icon);
        Session::setFlash('success', "Category '{$name}' created.");
        View::redirect('/settings');
    }

    public function deleteCategory(int $id): void
    {
        $userId = Session::userId();
        Category::delete($id, $userId);

        Session::setFlash('success', 'Custom category deleted.');
        View::redirect('/settings');
    }
}
