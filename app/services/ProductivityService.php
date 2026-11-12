<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use PDO;

class ProductivityService
{
    /**
     * Compute comprehensive user productivity statistics for dashboard.
     */
    public static function getMetricsForUser(int $userId): array
    {
        $db = Database::getInstance();

        // 1. Task counts by status (active only)
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
            FROM tasks
            WHERE user_id = :user_id AND archived_at IS NULL
        ");
        $stmt->execute([':user_id' => $userId]);
        $counts = $stmt->fetch() ?: ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0];

        $total = (int) ($counts['total'] ?? 0);
        $pending = (int) ($counts['pending'] ?? 0);
        $inProgress = (int) ($counts['in_progress'] ?? 0);
        $completed = (int) ($counts['completed'] ?? 0);

        // 2. Overdue tasks count
        $today = date('Y-m-d');
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM tasks 
            WHERE user_id = :user_id 
              AND archived_at IS NULL 
              AND status != 'completed' 
              AND due_date IS NOT NULL 
              AND due_date < :today
        ");
        $stmt->execute([':user_id' => $userId, ':today' => $today]);
        $overdue = (int) $stmt->fetchColumn();

        // 3. Completion rate percentage
        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

        // 4. Weekly completion trend (last 7 days)
        $weeklyTrend = self::getWeeklyCompletionTrend($userId);

        // 5. Category breakdown
        $categoryBreakdown = self::getCategoryDistribution($userId);

        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'overdue' => $overdue,
            'completion_rate' => $completionRate,
            'weekly_trend' => $weeklyTrend,
            'category_breakdown' => $categoryBreakdown,
        ];
    }

    /**
     * Get completion count per day for the last 7 days.
     */
    private static function getWeeklyCompletionTrend(int $userId): array
    {
        $db = Database::getInstance();
        $labels = [];
        $data = [];

        // Prepare the 7 days array
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $displayLabel = date('D (m/d)', strtotime("-{$i} days"));
            $labels[] = $displayLabel;
            $data[$date] = 0;
        }

        // Query activity logs for completed tasks in the last 7 days
        $startDate = date('Y-m-d 00:00:00', strtotime('-6 days'));
        $stmt = $db->prepare("
            SELECT created_at 
            FROM activity_logs 
            WHERE user_id = :user_id 
              AND action_type = 'completed'
              AND created_at >= :start_date
        ");
        $stmt->execute([':user_id' => $userId, ':start_date' => $startDate]);
        $logs = $stmt->fetchAll();

        foreach ($logs as $log) {
            $logDate = substr((string) $log['created_at'], 0, 10);
            if (isset($data[$logDate])) {
                $data[$logDate]++;
            }
        }

        return [
            'labels' => $labels,
            'values' => array_values($data),
        ];
    }

    /**
     * Get task distribution by category for doughnut chart.
     */
    private static function getCategoryDistribution(int $userId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT COALESCE(c.name, 'Uncategorized') AS cat_name, 
                   COALESCE(c.color, '#94a3b8') AS cat_color, 
                   COUNT(t.id) AS task_count
            FROM tasks t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = :user_id AND t.archived_at IS NULL
            GROUP BY c.name, c.color
            ORDER BY task_count DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll();

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($rows as $row) {
            $labels[] = $row['cat_name'];
            $values[] = (int) $row['task_count'];
            $colors[] = $row['cat_color'];
        }

        if (empty($labels)) {
            $labels = ['No tasks'];
            $values = [1];
            $colors = ['#e2e8f0'];
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
        ];
    }
}
