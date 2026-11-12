<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;
use PDO;

class Notification
{
    public static function create(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, type, is_read, link, created_at)
            VALUES (:user_id, :title, :message, :type, 0, :link, CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':title' => trim($title),
            ':message' => trim($message),
            ':type' => $type,
            ':link' => $link,
        ]);

        return (int) $db->lastInsertId();
    }

    public static function getAllForUser(int $userId, int $limit = 50): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM notifications 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getUnreadCount(int $userId): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
        $stmt->execute([':user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public static function markAsRead(int $id, int $userId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    public static function markAllAsRead(int $userId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
        return $stmt->execute([':user_id' => $userId]);
    }

    public static function existsToday(int $userId, string $title): bool
    {
        $db = Database::getInstance();
        $today = date('Y-m-d');
        // Comparing with substring/date prefix
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE user_id = :user_id 
              AND title = :title 
              AND created_at LIKE :today
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':today' => "{$today}%",
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
