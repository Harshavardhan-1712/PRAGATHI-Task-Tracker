<?php

declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;

/**
 * Class Database
 * Thread-safe PDO Singleton connection manager supporting MySQL and portable SQLite fallback.
 */
class Database
{
    private static ?PDO $instance = null;
    private static string $activeDriver = 'mysql';

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }

        return self::$instance;
    }

    public static function getDriver(): string
    {
        if (self::$instance === null) {
            self::connect();
        }

        return self::$activeDriver;
    }

    /**
     * Explicitly override or set connection instance (useful for unit testing)
     */
    public static function setInstance(PDO $pdo, string $driver = 'sqlite'): void
    {
        self::$instance = $pdo;
        self::$activeDriver = $driver;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    private static function connect(): void
    {
        $config = require __DIR__ . '/../config/database.php';
        $driver = $config['default'] ?? 'mysql';

        if ($driver === 'mysql') {
            try {
                $c = $config['connections']['mysql'];
                $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset={$c['charset']}";
                self::$instance = new PDO($dsn, $c['username'], $c['password'], $c['options']);
                self::$activeDriver = 'mysql';
                return;
            } catch (PDOException $e) {
                // If MySQL connection fails and fallback to SQLite is enabled
                if (!empty($config['fallback_sqlite'])) {
                    self::connectSqlite($config['connections']['sqlite']);
                    return;
                }
                throw $e;
            }
        }

        self::connectSqlite($config['connections']['sqlite']);
    }

    private static function connectSqlite(array $sqliteConfig): void
    {
        $dbPath = $sqliteConfig['database'];
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $initNeeded = !file_exists($dbPath) || filesize($dbPath) === 0;

        self::$instance = new PDO("sqlite:{$dbPath}", null, null, $sqliteConfig['options']);
        self::$instance->exec('PRAGMA foreign_keys = ON;');
        self::$activeDriver = 'sqlite';

        // Auto-initialize schema and demo data if newly created
        if ($initNeeded) {
            $schemaFile = __DIR__ . '/../../database/schema.sqlite.sql';
            if (file_exists($schemaFile)) {
                self::$instance->exec(file_get_contents($schemaFile));

                // Insert demo accounts with fresh bcrypt hash
                $pwd = password_hash('Pragathi@2026', PASSWORD_BCRYPT);
                $stmt = self::$instance->prepare("INSERT OR IGNORE INTO users (id, name, email, password, role, created_at) VALUES 
                    (1, 'Arjun Varma', 'arjun@pragathi.dev', :pwd1, 'intern', datetime('now', '-10 days')),
                    (2, 'Priya Sharma', 'priya@pragathi.dev', :pwd2, 'intern', datetime('now', '-10 days'))");
                $stmt->execute([':pwd1' => $pwd, ':pwd2' => $pwd]);

                // Insert sample tasks for Arjun
                $stmt = self::$instance->prepare("INSERT OR IGNORE INTO tasks (id, user_id, category_id, title, description, status, priority, due_date, tags, created_at, archived_at) VALUES
                    (1, 1, 1, 'Build Core PHP Authentication System', 'Implement session-based authentication with bcrypt hashing and CSRF tokens for the RTIH assessment.', 'completed', 'high', date('now', '-7 days'), 'security,php,rtih', datetime('now', '-10 days'), NULL),
                    (2, 1, 1, 'Design Normalized Relational MySQL Schema', 'Create tables for users, tasks, categories, activity logs, and notifications with foreign keys.', 'completed', 'high', date('now', '-6 days'), 'mysql,database', datetime('now', '-10 days'), NULL),
                    (3, 1, 3, 'Prepare RTIH Technical Submission Report', 'Write detailed architectural documentation, test cases, and XAMPP deployment guide.', 'in_progress', 'high', date('now', '+3 days'), 'rtih,documentation', datetime('now', '-5 days'), NULL),
                    (4, 1, 2, 'Implement Interactive Kanban Board', 'Create 3-column drag-and-drop board with asynchronous status persistence via Fetch API.', 'in_progress', 'medium', date('now', '+2 days'), 'javascript,ui,kanban', datetime('now', '-4 days'), NULL),
                    (5, 1, 4, 'Research Chart.js Analytics Integrations', 'Add productivity trend graphs and status breakdown charts for the overview dashboard.', 'pending', 'medium', date('now', '+6 days'), 'analytics,charts', datetime('now', '-3 days'), NULL),
                    (6, 1, 1, 'Setup Automated CLI Test Suite', 'Implement standalone test runner verifying all 20 required verification scenarios.', 'pending', 'high', date('now', '+1 days'), 'testing,qa,php', datetime('now', '-3 days'), NULL),
                    (7, 1, 5, 'Morning Workout & Reflection Routine', 'Daily exercise and priority alignment for enhanced productivity.', 'pending', 'low', date('now', '+8 days'), 'wellness,habits', datetime('now', '-2 days'), NULL),
                    (8, 1, 1, 'Legacy Prototype Cleanup', 'Archived task demonstrating archive and restore functionality.', 'completed', 'low', date('now', '-18 days'), 'cleanup', datetime('now', '-23 days'), datetime('now', '-17 days')),
                    (9, 2, 2, 'Priya Private Task: UI Style Guide', 'This task belongs strictly to Priya and must never be visible or editable by Arjun.', 'in_progress', 'high', date('now', '+4 days'), 'confidential,design', datetime('now', '-7 days'), NULL),
                    (10, 2, 1, 'Priya API Architecture Review', 'Evaluating microservice communication protocols.', 'pending', 'medium', date('now', '+10 days'), 'backend', datetime('now', '-6 days'), NULL)");
                $stmt->execute();

                // Insert activity logs for Arjun
                $stmt = self::$instance->prepare("INSERT OR IGNORE INTO activity_logs (id, user_id, task_id, action_type, description, created_at) VALUES
                    (1, 1, 1, 'created', 'Created task \"Build Core PHP Authentication System\"', datetime('now', '-10 days')),
                    (2, 1, 1, 'completed', 'Completed task \"Build Core PHP Authentication System\"', datetime('now', '-7 days')),
                    (3, 1, 2, 'created', 'Created task \"Design Normalized Relational MySQL Schema\"', datetime('now', '-10 days')),
                    (4, 1, 2, 'completed', 'Completed task \"Design Normalized Relational MySQL Schema\"', datetime('now', '-6 days')),
                    (5, 1, 3, 'created', 'Created task \"Prepare RTIH Technical Submission Report\"', datetime('now', '-5 days')),
                    (6, 1, 3, 'status_changed', 'Changed status of \"Prepare RTIH Technical Submission Report\" to In Progress', datetime('now', '-4 days')),
                    (7, 1, 4, 'created', 'Created task \"Implement Interactive Kanban Board\"', datetime('now', '-4 days'))");
                $stmt->execute();

                // Insert notifications for Arjun
                $stmt = self::$instance->prepare("INSERT OR IGNORE INTO notifications (id, user_id, title, message, type, is_read, link, created_at) VALUES
                    (1, 1, 'Welcome to PRAGATHI!', 'Turn Intentions Into Accomplishments. Your productivity workspace is active.', 'success', 1, '/dashboard', datetime('now', '-10 days')),
                    (2, 1, 'Upcoming Deadline Alert', 'Task \"Setup Automated CLI Test Suite\" is due soon.', 'warning', 0, '/tasks', datetime('now', '-1 hours')),
                    (3, 1, 'Milestone Reached', 'You completed 2 high-priority engineering tasks this week!', 'info', 0, '/dashboard', datetime('now', '-6 days'))");
                $stmt->execute();
            }
        }
    }
}
