<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Session;
use App\Helpers\View;
use App\Models\ActivityLog;
use App\Models\Notification;

class ActivityController
{
    public function index(): void
    {
        $userId = Session::userId();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $totalLogs = ActivityLog::countForUser($userId);
        $totalPages = max(1, (int) ceil($totalLogs / $perPage));
        $activities = ActivityLog::getRecentForUser($userId, $perPage, $offset);

        $unreadCount = Notification::getUnreadCount($userId);

        View::render('activity.index', [
            'pageTitle' => 'Activity Timeline — PRAGATHI',
            'activeNav' => 'activity',
            'activities' => $activities,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalLogs' => $totalLogs,
            'unreadNotificationCount' => $unreadCount,
        ]);
    }
}
