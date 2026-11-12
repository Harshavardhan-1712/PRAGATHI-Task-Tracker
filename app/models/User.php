<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;
use PDO;

class User
{
    public static function create(string $name, string $email, string $password, string $role = 'user'): int
    {
        $db = Database::getInstance();
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $stmt->execute([
            ':name' => $name,
            ':email' => strtolower(trim($email)),
            ':password' => $hash,
            ':role' => $role,
        ]);

        return (int) $db->lastInsertId();
    }

    public static function findByEmail(string $email): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => strtolower(trim($email))]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, name, email, role, theme_preference, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function updatePassword(int $id, string $newPassword): bool
    {
        $db = Database::getInstance();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET password = :hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute([':hash' => $hash, ':id' => $id]);
    }

    public static function updateProfile(int $id, string $name, string $theme = 'light'): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET name = :name, theme_preference = :theme, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute([
            ':name' => $name,
            ':theme' => $theme,
            ':id' => $id,
        ]);
    }
}
