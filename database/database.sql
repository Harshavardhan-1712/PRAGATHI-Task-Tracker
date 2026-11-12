-- ============================================================
-- PRAGATHI - Intelligent Task & Productivity Management Platform
-- Database Schema for MySQL 8.0+ / MariaDB 10.4+
-- Compatible with XAMPP (Apache + MySQL/MariaDB)
-- Character Set: utf8mb4 (Full Unicode Support)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `pragathi_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pragathi_db`;

-- Disable foreign key checks during setup
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Table: users
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `tasks`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'user',
    `theme_preference` VARCHAR(10) NOT NULL DEFAULT 'light',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: categories
-- ------------------------------------------------------------
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL, -- NULL indicates system-wide default category
    `name` VARCHAR(50) NOT NULL,
    `color` VARCHAR(20) NOT NULL DEFAULT '#6366f1',
    `icon` VARCHAR(50) NOT NULL DEFAULT 'bi-folder',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_categories_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: tasks
-- ------------------------------------------------------------
CREATE TABLE `tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `category_id` INT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    `priority` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    `due_date` DATE NULL,
    `tags` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `archived_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_tasks_user_status` (`user_id`, `status`),
    INDEX `idx_tasks_user_due_date` (`user_id`, `due_date`),
    INDEX `idx_tasks_user_archived` (`user_id`, `archived_at`),
    INDEX `idx_tasks_user_priority` (`user_id`, `priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: activity_logs
-- ------------------------------------------------------------
CREATE TABLE `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `task_id` INT NULL,
    `action_type` VARCHAR(50) NOT NULL, -- created, updated, status_changed, completed, deleted, archived, restored
    `description` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE SET NULL,
    INDEX `idx_activity_user_created` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: notifications
-- ------------------------------------------------------------
CREATE TABLE `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `type` VARCHAR(30) NOT NULL DEFAULT 'info', -- info, warning, success, danger
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `link` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_notifications_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Seed: Default Categories
-- ------------------------------------------------------------
INSERT INTO `categories` (`id`, `user_id`, `name`, `color`, `icon`) VALUES
(1, NULL, 'Engineering & Tech', '#3b82f6', 'bi-code-slash'),
(2, NULL, 'Product & Design', '#8b5cf6', 'bi-palette'),
(3, NULL, 'Operations & RTIH', '#10b981', 'bi-building-check'),
(4, NULL, 'Research & Notes', '#f59e0b', 'bi-lightbulb'),
(5, NULL, 'Personal & Health', '#ec4899', 'bi-heart-pulse');

-- ------------------------------------------------------------
-- Seed: Demo Users (Password: Pragathi@2026)
-- Hash generated via password_hash('Pragathi@2026', PASSWORD_BCRYPT)
-- ------------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Arjun Varma', 'arjun@pragathi.dev', '$2y$10$/j0tczZ83Yk47QVzepnP3eiUU4RIHn3vhv1em96r.RTwlqhOQ4McO', 'intern', '2026-11-01 09:00:00'),
(2, 'Priya Sharma', 'priya@pragathi.dev', '$2y$10$/j0tczZ83Yk47QVzepnP3eiUU4RIHn3vhv1em96r.RTwlqhOQ4McO', 'intern', '2026-11-01 10:00:00');

-- ------------------------------------------------------------
-- Seed: Tasks for Arjun (User 1)
-- Diverse statuses, priorities, due dates
-- ------------------------------------------------------------
INSERT INTO `tasks` (`id`, `user_id`, `category_id`, `title`, `description`, `status`, `priority`, `due_date`, `tags`, `created_at`, `archived_at`) VALUES
(1, 1, 1, 'Build Core PHP Authentication System', 'Implement session-based authentication with bcrypt hashing and CSRF tokens for the RTIH assessment.', 'completed', 'high', '2026-11-05', 'security,php,rtih', '2026-11-02 10:15:00', NULL),
(2, 1, 1, 'Design Normalized Relational MySQL Schema', 'Create tables for users, tasks, categories, activity logs, and notifications with foreign keys.', 'completed', 'high', '2026-11-06', 'mysql,database', '2026-11-02 11:30:00', NULL),
(3, 1, 3, 'Prepare RTIH Technical Submission Report', 'Write detailed architectural documentation, test cases, and XAMPP deployment guide.', 'in_progress', 'high', '2026-11-15', 'rtih,documentation', '2026-11-07 14:00:00', NULL),
(4, 1, 2, 'Implement Interactive Kanban Board', 'Create 3-column drag-and-drop board with asynchronous status persistence via Fetch API.', 'in_progress', 'medium', '2026-11-14', 'javascript,ui,kanban', '2026-11-08 16:20:00', NULL),
(5, 1, 4, 'Research Chart.js Analytics Integrations', 'Add productivity trend graphs and status breakdown charts for the overview dashboard.', 'pending', 'medium', '2026-11-18', 'analytics,charts', '2026-11-09 09:45:00', NULL),
(6, 1, 1, 'Setup Automated CLI Test Suite', 'Implement standalone test runner verifying all 20 required verification scenarios.', 'pending', 'high', '2026-11-13', 'testing,qa,php', '2026-11-09 13:00:00', NULL),
(7, 1, 5, 'Morning Workout & Reflection Routine', 'Daily exercise and priority alignment for enhanced productivity.', 'pending', 'low', '2026-11-20', 'wellness,habits', '2026-11-10 07:00:00', NULL),
(8, 1, 1, 'Legacy Prototype Cleanup', 'Archived task demonstrating archive and restore functionality.', 'completed', 'low', '2026-10-25', 'cleanup', '2026-10-20 08:00:00', '2026-10-26 12:00:00');

-- ------------------------------------------------------------
-- Seed: Tasks for Priya (User 2 - Multi-User Isolation Test)
-- ------------------------------------------------------------
INSERT INTO `tasks` (`id`, `user_id`, `category_id`, `title`, `description`, `status`, `priority`, `due_date`, `tags`, `created_at`, `archived_at`) VALUES
(9, 2, 2, 'Priya Private Task: UI Style Guide', 'This task belongs strictly to Priya and must never be visible or editable by Arjun.', 'in_progress', 'high', '2026-11-16', 'confidential,design', '2026-11-05 10:00:00', NULL),
(10, 2, 1, 'Priya API Architecture Review', 'Evaluating microservice communication protocols.', 'pending', 'medium', '2026-11-22', 'backend', '2026-11-06 14:30:00', NULL);

-- ------------------------------------------------------------
-- Seed: Activity Logs for Arjun (User 1)
-- ------------------------------------------------------------
INSERT INTO `activity_logs` (`id`, `user_id`, `task_id`, `action_type`, `description`, `created_at`) VALUES
(1, 1, 1, 'created', 'Created task "Build Core PHP Authentication System"', '2026-11-02 10:15:00'),
(2, 1, 1, 'completed', 'Completed task "Build Core PHP Authentication System"', '2026-11-05 16:30:00'),
(3, 1, 2, 'created', 'Created task "Design Normalized Relational MySQL Schema"', '2026-11-02 11:30:00'),
(4, 1, 2, 'completed', 'Completed task "Design Normalized Relational MySQL Schema"', '2026-11-06 18:00:00'),
(5, 1, 3, 'created', 'Created task "Prepare RTIH Technical Submission Report"', '2026-11-07 14:00:00'),
(6, 1, 3, 'status_changed', 'Changed status of "Prepare RTIH Technical Submission Report" to In Progress', '2026-11-08 09:00:00'),
(7, 1, 4, 'created', 'Created task "Implement Interactive Kanban Board"', '2026-11-08 16:20:00');

-- ------------------------------------------------------------
-- Seed: Notifications for Arjun (User 1)
-- ------------------------------------------------------------
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES
(1, 1, 'Welcome to PRAGATHI!', 'Turn Intentions Into Accomplishments. Your productivity workspace is active.', 'success', 1, '/dashboard', '2026-11-01 09:05:00'),
(2, 1, 'Upcoming Deadline Alert', 'Task "Setup Automated CLI Test Suite" is due soon.', 'warning', 0, '/tasks', '2026-11-12 08:00:00'),
(3, 1, 'Milestone Reached', 'You completed 2 high-priority engineering tasks this week!', 'info', 0, '/dashboard', '2026-11-06 18:05:00');
