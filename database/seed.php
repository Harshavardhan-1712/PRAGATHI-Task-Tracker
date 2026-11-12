<?php
/**
 * PRAGATHI Database Seeder
 * Populates tables with fresh schema and demo accounts.
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/Database.php';

use App\Helpers\Database;

echo "Seeding PRAGATHI database...\n";

try {
    $db = Database::getInstance();
    $driver = Database::getDriver();

    // Load appropriate schema
    if ($driver === 'sqlite') {
        $sql = file_get_contents(__DIR__ . '/schema.sqlite.sql');
        $db->exec($sql);
    } else {
        $sql = file_get_contents(__DIR__ . '/database.sql');
        $db->exec($sql);
    }

    // Ensure demo password hash is freshly generated
    $passwordHash = password_hash('Pragathi@2026', PASSWORD_BCRYPT);

    // Update users with freshly computed hash
    $stmt = $db->prepare("UPDATE users SET password = :pwd WHERE email IN ('arjun@pragathi.dev', 'priya@pragathi.dev')");
    $stmt->execute([':pwd' => $passwordHash]);

    echo "Database seeded successfully!\n";
    echo "Driver: {$driver}\n";
    echo "Demo Account 1: arjun@pragathi.dev / Pragathi@2026\n";
    echo "Demo Account 2: priya@pragathi.dev / Pragathi@2026\n";
} catch (\Throwable $e) {
    echo "Seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}
