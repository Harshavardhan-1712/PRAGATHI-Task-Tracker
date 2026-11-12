<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class Category
{
    public static function getAllForUser(?int $userId): array
    {
        $db = Database::getInstance();
        if ($userId) {
            $stmt = $db->prepare("SELECT * FROM categories WHERE user_id IS NULL OR user_id = :user_id ORDER BY id ASC");
            $stmt->execute([':user_id' => $userId]);
        } else {
            $stmt = $db->query("SELECT * FROM categories WHERE user_id IS NULL ORDER BY id ASC");
        }

        return $stmt->fetchAll();
    }

    public static function findById(int $id, ?int $userId = null): ?array
    {
        $db = Database::getInstance();
        if ($userId) {
            $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id AND (user_id IS NULL OR user_id = :user_id) LIMIT 1");
            $stmt->execute([':id' => $id, ':user_id' => $userId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
        }

        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(int $userId, string $name, string $color = '#6366f1', string $icon = 'bi-folder'): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO categories (user_id, name, color, icon) VALUES (:user_id, :name, :color, :icon)");
        $stmt->execute([
            ':user_id' => $userId,
            ':name' => trim($name),
            ':color' => trim($color),
            ':icon' => trim($icon),
        ]);

        return (int) $db->lastInsertId();
    }

    public static function delete(int $id, int $userId): bool
    {
        $db = Database::getInstance();
        // Users can only delete their custom categories, not system defaults (where user_id IS NULL)
        $stmt = $db->prepare("DELETE FROM categories WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }
}
