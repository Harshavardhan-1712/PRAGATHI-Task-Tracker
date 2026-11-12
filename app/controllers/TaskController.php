<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Session;
use App\Helpers\Validator;
use App\Helpers\View;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Task;

class TaskController
{
    public function index(): void
    {
        $userId = Session::userId();

        $params = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'sort' => $_GET['sort'] ?? 'due_date_asc',
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(50, max(5, (int) ($_GET['per_page'] ?? 10)));
        $offset = ($page - 1) * $perPage;

        $totalTasks = Task::countForUser($userId, $params);
        $totalPages = max(1, (int) ceil($totalTasks / $perPage));
        $tasks = Task::getAllForUser($userId, $params, $perPage, $offset);

        $categories = Category::getAllForUser($userId);
        $unreadCount = Notification::getUnreadCount($userId);

        View::render('tasks.index', [
            'pageTitle' => 'My Tasks — PRAGATHI',
            'activeNav' => 'tasks',
            'tasks' => $tasks,
            'categories' => $categories,
            'params' => $params,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalTasks' => $totalTasks,
            'perPage' => $perPage,
            'unreadNotificationCount' => $unreadCount,
        ]);
    }

    public function create(): void
    {
        $userId = Session::userId();
        $categories = Category::getAllForUser($userId);
        $unreadCount = Notification::getUnreadCount($userId);

        View::render('tasks.create', [
            'pageTitle' => 'Create New Task — PRAGATHI',
            'activeNav' => 'tasks',
            'categories' => $categories,
            'unreadNotificationCount' => $unreadCount,
        ]);
    }

    public function store(): void
    {
        $userId = Session::userId();

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status' => $_POST['status'] ?? 'pending',
            'priority' => $_POST['priority'] ?? 'medium',
            'category_id' => !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
            'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
            'tags' => trim($_POST['tags'] ?? ''),
            'user_id' => $userId,
        ];

        $validator = Validator::make($data, [
            'title' => ['required', 'min:3', 'max:255'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError() ?? 'Invalid task input.');
            View::redirect('/tasks/create');
            return;
        }

        $taskId = Task::create($data);

        // Record activity log
        ActivityLog::log($userId, $taskId, 'created', "Created task \"{$data['title']}\"");

        Session::setFlash('success', 'Task created successfully!');
        View::redirect('/tasks');
    }

    public function edit(int $id): void
    {
        $userId = Session::userId();
        $task = Task::findForUser($id, $userId);

        if (!$task) {
            Session::setFlash('error', 'Task not found or you do not have permission to access it.');
            View::redirect('/tasks');
            return;
        }

        $categories = Category::getAllForUser($userId);
        $unreadCount = Notification::getUnreadCount($userId);

        View::render('tasks.edit', [
            'pageTitle' => "Edit Task: {$task['title']} — PRAGATHI",
            'activeNav' => 'tasks',
            'task' => $task,
            'categories' => $categories,
            'unreadNotificationCount' => $unreadCount,
        ]);
    }

    public function update(int $id): void
    {
        $userId = Session::userId();
        $existingTask = Task::findForUser($id, $userId);

        if (!$existingTask) {
            Session::setFlash('error', 'Unauthorized action or task not found.');
            View::redirect('/tasks');
            return;
        }

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status' => $_POST['status'] ?? 'pending',
            'priority' => $_POST['priority'] ?? 'medium',
            'category_id' => !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
            'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
            'tags' => trim($_POST['tags'] ?? ''),
        ];

        $validator = Validator::make($data, [
            'title' => ['required', 'min:3', 'max:255'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError() ?? 'Invalid task input.');
            View::redirect("/tasks/{$id}/edit");
            return;
        }

        Task::update($id, $userId, $data);

        // Log status change or update
        if ($existingTask['status'] !== $data['status']) {
            if ($data['status'] === 'completed') {
                ActivityLog::log($userId, $id, 'completed', "Completed task \"{$data['title']}\"");
            } else {
                ActivityLog::log($userId, $id, 'status_changed', "Changed status of \"{$data['title']}\" to " . ucfirst(str_replace('_', ' ', $data['status'])));
            }
        } else {
            ActivityLog::log($userId, $id, 'updated', "Updated details for \"{$data['title']}\"");
        }

        Session::setFlash('success', 'Task updated successfully!');
        View::redirect('/tasks');
    }

    public function delete(int $id): void
    {
        $userId = Session::userId();
        $task = Task::findForUser($id, $userId);

        if (!$task) {
            Session::setFlash('error', 'Unauthorized action or task not found.');
            View::redirect('/tasks');
            return;
        }

        Task::delete($id, $userId);
        ActivityLog::log($userId, null, 'deleted', "Deleted task \"{$task['title']}\"");

        Session::setFlash('success', 'Task deleted successfully.');
        View::redirect('/tasks');
    }

    public function archive(int $id): void
    {
        $userId = Session::userId();
        $task = Task::findForUser($id, $userId);

        if (!$task) {
            Session::setFlash('error', 'Unauthorized action or task not found.');
            View::redirect('/tasks');
            return;
        }

        Task::archive($id, $userId);
        ActivityLog::log($userId, $id, 'archived', "Archived task \"{$task['title']}\"");

        Session::setFlash('success', 'Task archived successfully.');
        View::redirect('/tasks');
    }

    public function restore(int $id): void
    {
        $userId = Session::userId();
        $task = Task::findForUser($id, $userId);

        if (!$task) {
            Session::setFlash('error', 'Unauthorized action or task not found.');
            View::redirect('/tasks/archived');
            return;
        }

        Task::restore($id, $userId);
        ActivityLog::log($userId, $id, 'restored', "Restored task \"{$task['title']}\"");

        Session::setFlash('success', 'Task restored to active list.');
        View::redirect('/tasks');
    }

    public function archived(): void
    {
        $userId = Session::userId();
        $params = ['archived' => true];
        $tasks = Task::getAllForUser($userId, $params, 100, 0);
        $unreadCount = Notification::getUnreadCount($userId);

        View::render('tasks.archived', [
            'pageTitle' => 'Archived Tasks — PRAGATHI',
            'activeNav' => 'tasks',
            'tasks' => $tasks,
            'unreadNotificationCount' => $unreadCount,
        ]);
    }

    public function bulkAction(): void
    {
        $userId = Session::userId();
        $action = $_POST['bulk_action'] ?? '';
        $selectedIds = $_POST['task_ids'] ?? [];

        if (empty($selectedIds) || !is_array($selectedIds)) {
            Session::setFlash('warning', 'Please select at least one task.');
            View::redirect('/tasks');
            return;
        }

        $count = count($selectedIds);

        switch ($action) {
            case 'status_pending':
                Task::bulkUpdateStatus($selectedIds, $userId, 'pending');
                ActivityLog::log($userId, null, 'status_changed', "Updated {$count} tasks to Pending");
                Session::setFlash('success', "Marked {$count} tasks as Pending.");
                break;

            case 'status_in_progress':
                Task::bulkUpdateStatus($selectedIds, $userId, 'in_progress');
                ActivityLog::log($userId, null, 'status_changed', "Updated {$count} tasks to In Progress");
                Session::setFlash('success', "Marked {$count} tasks as In Progress.");
                break;

            case 'status_completed':
                Task::bulkUpdateStatus($selectedIds, $userId, 'completed');
                ActivityLog::log($userId, null, 'completed', "Completed {$count} tasks in bulk");
                Session::setFlash('success', "Marked {$count} tasks as Completed.");
                break;

            case 'archive':
                Task::bulkArchive($selectedIds, $userId);
                ActivityLog::log($userId, null, 'archived', "Archived {$count} tasks in bulk");
                Session::setFlash('success', "Archived {$count} tasks.");
                break;

            case 'delete':
                Task::bulkDelete($selectedIds, $userId);
                ActivityLog::log($userId, null, 'deleted', "Deleted {$count} tasks in bulk");
                Session::setFlash('success', "Permanently deleted {$count} tasks.");
                break;

            default:
                Session::setFlash('error', 'Invalid bulk action requested.');
        }

        View::redirect('/tasks');
    }

    public function exportCsv(): void
    {
        $userId = Session::userId();
        $params = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
        ];

        $tasks = Task::getAllForExport($userId, $params);
        $filename = 'pragathi_tasks_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel compatibility
        fputs($out, "\xEF\xBB\xBF");

        // Headers
        fputcsv($out, ['Task ID', 'Title', 'Description', 'Status', 'Priority', 'Category', 'Due Date', 'Tags', 'Created At', 'Updated At']);

        foreach ($tasks as $t) {
            fputcsv($out, [
                $t['id'],
                $t['title'],
                $t['description'] ?? '',
                ucfirst(str_replace('_', ' ', $t['status'])),
                ucfirst($t['priority']),
                $t['category'] ?? 'Uncategorized',
                $t['due_date'] ?? 'None',
                $t['tags'] ?? '',
                $t['created_at'],
                $t['updated_at'],
            ]);
        }

        fclose($out);
        exit;
    }
}
