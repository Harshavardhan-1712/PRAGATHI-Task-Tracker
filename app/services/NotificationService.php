<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;

class NotificationService
{
    /**
     * Check upcoming and overdue tasks for user, triggering notifications if not already sent today.
     */
    public static function checkAndGenerateNotifications(int $userId): void
    {
        // 1. Overdue tasks check
        $overdueTasks = Task::getOverdueTasks($userId);
        foreach ($overdueTasks as $task) {
            $alertTitle = "Overdue Alert: {$task['title']}";
            if (!Notification::existsToday($userId, $alertTitle)) {
                Notification::create(
                    $userId,
                    $alertTitle,
                    "Task '{$task['title']}' was due on {$task['due_date']} and requires attention.",
                    'warning',
                    '/tasks'
                );
            }
        }

        // 2. Upcoming tasks (due today)
        $upcoming = Task::getUpcomingDeadlines($userId, 10);
        $today = date('Y-m-d');
        foreach ($upcoming as $task) {
            if ($task['due_date'] === $today) {
                $alertTitle = "Due Today: {$task['title']}";
                if (!Notification::existsToday($userId, $alertTitle)) {
                    Notification::create(
                        $userId,
                        $alertTitle,
                        "Task '{$task['title']}' has its deadline today. Turn intentions into accomplishments!",
                        'info',
                        '/tasks'
                    );
                }
            }
        }
    }
}
