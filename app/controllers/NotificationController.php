<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Session;
use App\Helpers\View;
use App\Models\Notification;

class NotificationController
{
    public function index(): void
    {
        $userId = Session::userId();
        $notifications = Notification::getAllForUser($userId, 50);
        $unreadCount = Notification::getUnreadCount($userId);

        View::render('notifications.index', [
            'pageTitle' => 'Notification Center — PRAGATHI',
            'activeNav' => 'notifications',
            'notifications' => $notifications,
            'unreadNotificationCount' => $unreadCount,
        ]);
    }

    public function markAsRead(int $id): void
    {
        $userId = Session::userId();
        Notification::markAsRead($id, $userId);

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));

        if ($isAjax) {
            View::json(['success' => true]);
            return;
        }

        View::redirect('/notifications');
    }

    public function markAllAsRead(): void
    {
        $userId = Session::userId();
        Notification::markAllAsRead($userId);

        Session::setFlash('success', 'All notifications marked as read.');
        View::redirect('/notifications');
    }
}
