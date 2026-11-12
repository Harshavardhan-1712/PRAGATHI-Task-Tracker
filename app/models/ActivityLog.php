<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;
use PDO;

class ActivityLog
{
    public static function log(int $userId, ?int $taskId, string $actionType, string $description): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, task_id, action_type, description, created_at)
            VALUES (:user_id, :task_id, :action_type, :description, CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':task_id' => $taskId,
            ':action_type' => $actionType,
            ':description' => trim($description),
        ]);

        return (int) $db->lastInsertId();
    }

    public static function getRecentForUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT a.*, t.title AS task_title
            FROM activity_logs a
            LEFT JOIN tasks t ON a.task_id = t.id
            WHERE a.user_id = :user_id
            ORDER BY a.created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function countForUser(int $userId): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM activity_logs WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }
}
