<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Session;
use App\Helpers\View;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Task;

class KanbanController
{
    public function index(): void
    {
        $userId = Session::userId();
        $boardTasks = Task::getKanbanTasksForUser($userId);
        $unreadCount = Notification::getUnreadCount($userId);

        View::render('kanban.index', [
            'pageTitle' => 'Kanban Board — PRAGATHI',
            'activeNav' => 'kanban',
            'boardTasks' => $boardTasks,
            'unreadNotificationCount' => $unreadCount,
        ]);
    }

    public function updateStatusAjax(): void
    {
        $userId = Session::userId();

        // Support both application/json and form-encoded data
        $rawInput = file_get_contents('php://input');
        $input = [];
        if (!empty($rawInput)) {
            $input = json_decode($rawInput, true) ?: [];
        }

        $id = (int) ($input['task_id'] ?? $_POST['task_id'] ?? 0);
        $status = (string) ($input['status'] ?? $_POST['status'] ?? '');

        if (!$id || !in_array($status, ['pending', 'in_progress', 'completed'], true)) {
            View::json(['success' => false, 'error' => 'Invalid task ID or status.'], 400);
            return;
        }

        $task = Task::findForUser($id, $userId);
        if (!$task) {
            View::json(['success' => false, 'error' => 'Task not found or access denied.'], 403);
            return;
        }

        $oldStatus = $task['status'];
        if ($oldStatus !== $status) {
            Task::updateStatus($id, $userId, $status);

            if ($status === 'completed') {
                ActivityLog::log($userId, $id, 'completed', "Completed task \"{$task['title']}\" on Kanban board");
            } else {
                $statusName = ucfirst(str_replace('_', ' ', $status));
                ActivityLog::log($userId, $id, 'status_changed', "Moved \"{$task['title']}\" to {$statusName} on Kanban");
            }
        }

        View::json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'task_id' => $id,
            'new_status' => $status,
        ]);
    }
}
