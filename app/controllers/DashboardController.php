<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Session;
use App\Helpers\View;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Task;
use App\Services\NotificationService;
use App\Services\ProductivityService;

class DashboardController
{
    public function index(): void
    {
        $userId = Session::userId();

        // Evaluate automated deadline/overdue notification checks
        NotificationService::checkAndGenerateNotifications($userId);

        $metrics = ProductivityService::getMetricsForUser($userId);
        $upcomingDeadlines = Task::getUpcomingDeadlines($userId, 5);
        $recentTasks = Task::getRecentTasks($userId, 5);
        $recentActivities = ActivityLog::getRecentForUser($userId, 6);
        $categories = Category::getAllForUser($userId);
        $unreadCount = Notification::getUnreadCount($userId);

        View::render('dashboard.index', [
            'pageTitle' => 'Productivity Overview — PRAGATHI',
            'activeNav' => 'dashboard',
            'metrics' => $metrics,
            'upcomingDeadlines' => $upcomingDeadlines,
            'recentTasks' => $recentTasks,
            'recentActivities' => $recentActivities,
            'categories' => $categories,
            'unreadNotificationCount' => $unreadCount,
        ]);
    }
}
