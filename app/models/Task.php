<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;
use PDO;

class Task
{
    /**
     * Create a new task for the authenticated user.
     */
    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO tasks (user_id, category_id, title, description, status, priority, due_date, tags, created_at, updated_at)
            VALUES (:user_id, :category_id, :title, :description, :status, :priority, :due_date, :tags, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            ':user_id' => (int) $data['user_id'],
            ':category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            ':title' => trim($data['title']),
            ':description' => !empty($data['description']) ? trim($data['description']) : null,
            ':status' => $data['status'] ?? 'pending',
            ':priority' => $data['priority'] ?? 'medium',
            ':due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
            ':tags' => !empty($data['tags']) ? trim($data['tags']) : null,
        ]);

        return (int) $db->lastInsertId();
    }

    /**
     * Find a task by ID ensuring it belongs to the given user.
     * Prevents IDOR vulnerabilities.
     */
    public static function findForUser(int $id, int $userId): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT t.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
            FROM tasks t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.id = :id AND t.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $task = $stmt->fetch();

        return $task ?: null;
    }

    /**
     * Fetch paginated and filtered tasks for the user.
     */
    public static function getAllForUser(int $userId, array $params = [], int $limit = 10, int $offset = 0): array
    {
        $db = Database::getInstance();
        $where = ["t.user_id = :user_id"];
        $bindings = [':user_id' => $userId];

        // Archive status filter
        if (!empty($params['archived'])) {
            $where[] = "t.archived_at IS NOT NULL";
        } else {
            $where[] = "t.archived_at IS NULL";
        }

        // Search by title or description
        if (!empty($params['search'])) {
            $where[] = "(t.title LIKE :search OR t.description LIKE :search)";
            $bindings[':search'] = '%' . trim($params['search']) . '%';
        }

        // Status filter
        if (!empty($params['status']) && in_array($params['status'], ['pending', 'in_progress', 'completed'], true)) {
            $where[] = "t.status = :status";
            $bindings[':status'] = $params['status'];
        }

        // Priority filter
        if (!empty($params['priority']) && in_array($params['priority'], ['low', 'medium', 'high'], true)) {
            $where[] = "t.priority = :priority";
            $bindings[':priority'] = $params['priority'];
        }

        // Category filter
        if (!empty($params['category_id']) && is_numeric($params['category_id'])) {
            $where[] = "t.category_id = :category_id";
            $bindings[':category_id'] = (int) $params['category_id'];
        }

        // Sorting
        $allowedSorts = [
            'due_date_asc' => 't.due_date IS NULL, t.due_date ASC, t.id DESC',
            'due_date_desc' => 't.due_date DESC, t.id DESC',
            'priority_desc' => "CASE t.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END, t.due_date ASC",
            'priority_asc' => "CASE t.priority WHEN 'low' THEN 1 WHEN 'medium' THEN 2 WHEN 'high' THEN 3 ELSE 4 END, t.due_date ASC",
            'created_at_desc' => 't.created_at DESC',
            'created_at_asc' => 't.created_at ASC',
            'title_asc' => 't.title ASC',
        ];

        $sortKey = $params['sort'] ?? 'due_date_asc';
        $orderBy = $allowedSorts[$sortKey] ?? $allowedSorts['due_date_asc'];

        $whereClause = implode(' AND ', $where);
        $sql = "
            SELECT t.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
            FROM tasks t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $db->prepare($sql);
        foreach ($bindings as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Count total tasks matching the given filters for pagination.
     */
    public static function countForUser(int $userId, array $params = []): int
    {
        $db = Database::getInstance();
        $where = ["t.user_id = :user_id"];
        $bindings = [':user_id' => $userId];

        if (!empty($params['archived'])) {
            $where[] = "t.archived_at IS NOT NULL";
        } else {
            $where[] = "t.archived_at IS NULL";
        }

        if (!empty($params['search'])) {
            $where[] = "(t.title LIKE :search OR t.description LIKE :search)";
            $bindings[':search'] = '%' . trim($params['search']) . '%';
        }

        if (!empty($params['status']) && in_array($params['status'], ['pending', 'in_progress', 'completed'], true)) {
            $where[] = "t.status = :status";
            $bindings[':status'] = $params['status'];
        }

        if (!empty($params['priority']) && in_array($params['priority'], ['low', 'medium', 'high'], true)) {
            $where[] = "t.priority = :priority";
            $bindings[':priority'] = $params['priority'];
        }

        if (!empty($params['category_id']) && is_numeric($params['category_id'])) {
            $where[] = "t.category_id = :category_id";
            $bindings[':category_id'] = (int) $params['category_id'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) FROM tasks t WHERE {$whereClause}";

        $stmt = $db->prepare($sql);
        $stmt->execute($bindings);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Update task details with ownership check.
     */
    public static function update(int $id, int $userId, array $data): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE tasks 
            SET category_id = :category_id,
                title = :title,
                description = :description,
                status = :status,
                priority = :priority,
                due_date = :due_date,
                tags = :tags,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND user_id = :user_id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
            ':category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            ':title' => trim($data['title']),
            ':description' => !empty($data['description']) ? trim($data['description']) : null,
            ':status' => $data['status'] ?? 'pending',
            ':priority' => $data['priority'] ?? 'medium',
            ':due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
            ':tags' => !empty($data['tags']) ? trim($data['tags']) : null,
        ]);
    }

    /**
     * Update only task status (for Kanban drag & drop and quick actions).
     */
    public static function updateStatus(int $id, int $userId, string $status): bool
    {
        if (!in_array($status, ['pending', 'in_progress', 'completed'], true)) {
            return false;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE tasks 
            SET status = :status, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id AND user_id = :user_id
        ");

        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
            ':user_id' => $userId,
        ]);
    }

    /**
     * Soft delete: archive a task.
     */
    public static function archive(int $id, int $userId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE tasks 
            SET archived_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id AND user_id = :user_id AND archived_at IS NULL
        ");

        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /**
     * Restore an archived task.
     */
    public static function restore(int $id, int $userId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE tasks 
            SET archived_at = NULL, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id AND user_id = :user_id AND archived_at IS NOT NULL
        ");

        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /**
     * Hard delete a task with ownership check.
     */
    public static function delete(int $id, int $userId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /**
     * Bulk update status for multiple task IDs belonging to user.
     */
    public static function bulkUpdateStatus(array $ids, int $userId, string $status): int
    {
        if (empty($ids) || !in_array($status, ['pending', 'in_progress', 'completed'], true)) {
            return 0;
        }

        $db = Database::getInstance();
        $sanitizedIds = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));

        $sql = "UPDATE tasks SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND id IN ({$placeholders})";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge([$status, $userId], $sanitizedIds));

        return $stmt->rowCount();
    }

    /**
     * Bulk archive tasks.
     */
    public static function bulkArchive(array $ids, int $userId): int
    {
        if (empty($ids)) {
            return 0;
        }

        $db = Database::getInstance();
        $sanitizedIds = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));

        $sql = "UPDATE tasks SET archived_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND id IN ({$placeholders}) AND archived_at IS NULL";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge([$userId], $sanitizedIds));

        return $stmt->rowCount();
    }

    /**
     * Bulk delete tasks.
     */
    public static function bulkDelete(array $ids, int $userId): int
    {
        if (empty($ids)) {
            return 0;
        }

        $db = Database::getInstance();
        $sanitizedIds = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));

        $sql = "DELETE FROM tasks WHERE user_id = ? AND id IN ({$placeholders})";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge([$userId], $sanitizedIds));

        return $stmt->rowCount();
    }

    /**
     * Get tasks grouped for Kanban board.
     */
    public static function getKanbanTasksForUser(int $userId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT t.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
            FROM tasks t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = :user_id AND t.archived_at IS NULL
            ORDER BY CASE t.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END, t.due_date ASC
        ");
        $stmt->execute([':user_id' => $userId]);
        $allTasks = $stmt->fetchAll();

        $kanban = [
            'pending' => [],
            'in_progress' => [],
            'completed' => [],
        ];

        foreach ($allTasks as $task) {
            $status = $task['status'];
            if (isset($kanban[$status])) {
                $kanban[$status][] = $task;
            }
        }

        return $kanban;
    }

    /**
     * Get upcoming deadlines for dashboard.
     */
    public static function getUpcomingDeadlines(int $userId, int $limit = 5): array
    {
        $db = Database::getInstance();
        $today = date('Y-m-d');
        $stmt = $db->prepare("
            SELECT t.*, c.name AS category_name, c.color AS category_color
            FROM tasks t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = :user_id 
              AND t.archived_at IS NULL 
              AND t.status != 'completed'
              AND t.due_date IS NOT NULL
              AND t.due_date >= :today
            ORDER BY t.due_date ASC, CASE t.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':today', $today, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get overdue tasks for dashboard and notification trigger.
     */
    public static function getOverdueTasks(int $userId): array
    {
        $db = Database::getInstance();
        $today = date('Y-m-d');
        $stmt = $db->prepare("
            SELECT t.*, c.name AS category_name, c.color AS category_color
            FROM tasks t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = :user_id 
              AND t.archived_at IS NULL 
              AND t.status != 'completed'
              AND t.due_date IS NOT NULL 
              AND t.due_date < :today
            ORDER BY t.due_date ASC
        ");
        $stmt->execute([':user_id' => $userId, ':today' => $today]);

        return $stmt->fetchAll();
    }

    /**
     * Get recently updated tasks for dashboard.
     */
    public static function getRecentTasks(int $userId, int $limit = 5): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT t.*, c.name AS category_name, c.color AS category_color
            FROM tasks t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = :user_id AND t.archived_at IS NULL
            ORDER BY t.updated_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get tasks for calendar view in date range.
     */
    public static function getTasksForCalendar(int $userId, string $startDate, string $endDate): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT t.id, t.title, t.status, t.priority, t.due_date, c.name AS category_name, c.color AS category_color
            FROM tasks t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = :user_id 
              AND t.archived_at IS NULL 
              AND t.due_date IS NOT NULL
              AND t.due_date >= :start_date 
              AND t.due_date <= :end_date
            ORDER BY t.due_date ASC
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get all active tasks for CSV export.
     */
    public static function getAllForExport(int $userId, array $params = []): array
    {
        $db = Database::getInstance();
        $where = ["t.user_id = :user_id"];
        $bindings = [':user_id' => $userId];

        if (!empty($params['archived'])) {
            $where[] = "t.archived_at IS NOT NULL";
        } else {
            $where[] = "t.archived_at IS NULL";
        }

        if (!empty($params['status'])) {
            $where[] = "t.status = :status";
            $bindings[':status'] = $params['status'];
        }

        if (!empty($params['priority'])) {
            $where[] = "t.priority = :priority";
            $bindings[':priority'] = $params['priority'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "
            SELECT t.id, t.title, t.description, t.status, t.priority, t.due_date, t.tags, 
                   c.name AS category, t.created_at, t.updated_at
            FROM tasks t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE {$whereClause}
            ORDER BY t.created_at DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll();
    }
}
