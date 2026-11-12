<?php

declare(strict_types=1);

use App\Routes\Router;

$router = new Router();

// Root route redirect
$router->get('/', 'DashboardController@index', ['auth']);

// Authentication Routes
$router->get('/login', 'AuthController@showLogin', ['guest']);
$router->post('/login', 'AuthController@login', ['guest', 'csrf']);
$router->get('/register', 'AuthController@showRegister', ['guest']);
$router->post('/register', 'AuthController@register', ['guest', 'csrf']);
$router->post('/logout', 'AuthController@logout', ['auth', 'csrf']);
$router->get('/logout', 'AuthController@logout', ['auth']); // Convenience GET logout

// Dashboard
$router->get('/dashboard', 'DashboardController@index', ['auth']);

// Tasks Management (Mandatory Level 1-2 & Differentiators)
$router->get('/tasks', 'TaskController@index', ['auth']);
$router->get('/tasks/create', 'TaskController@create', ['auth']);
$router->post('/tasks', 'TaskController@store', ['auth', 'csrf']);
$router->get('/tasks/archived', 'TaskController@archived', ['auth']);
$router->post('/tasks/bulk', 'TaskController@bulkAction', ['auth', 'csrf']);
$router->get('/tasks/export', 'TaskController@exportCsv', ['auth']);
$router->get('/tasks/{id}/edit', 'TaskController@edit', ['auth']);
$router->post('/tasks/{id}/update', 'TaskController@update', ['auth', 'csrf']);
$router->post('/tasks/{id}/delete', 'TaskController@delete', ['auth', 'csrf']);
$router->post('/tasks/{id}/archive', 'TaskController@archive', ['auth', 'csrf']);
$router->post('/tasks/{id}/restore', 'TaskController@restore', ['auth', 'csrf']);

// Interactive Kanban Board
$router->get('/kanban', 'KanbanController@index', ['auth']);
$router->post('/kanban/status', 'KanbanController@updateStatusAjax', ['auth', 'csrf']);

// Interactive Monthly Calendar
$router->get('/calendar', 'CalendarController@index', ['auth']);

// Activity Timeline (Audit Trail)
$router->get('/activity', 'ActivityController@index', ['auth']);

// Notification Center
$router->get('/notifications', 'NotificationController@index', ['auth']);
$router->post('/notifications/read-all', 'NotificationController@markAllAsRead', ['auth', 'csrf']);
$router->post('/notifications/{id}/read', 'NotificationController@markAsRead', ['auth', 'csrf']);

// Settings & Preferences
$router->get('/settings', 'SettingsController@index', ['auth']);
$router->post('/settings/profile', 'SettingsController@updateProfile', ['auth', 'csrf']);
$router->post('/settings/password', 'SettingsController@updatePassword', ['auth', 'csrf']);
$router->post('/settings/category', 'SettingsController@createCategory', ['auth', 'csrf']);
$router->post('/settings/category/{id}/delete', 'SettingsController@deleteCategory', ['auth', 'csrf']);

return $router;
