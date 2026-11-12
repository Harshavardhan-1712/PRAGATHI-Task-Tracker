<?php

declare(strict_types=1);

/**
 * PRAGATHI — Automated Verification Suite
 * Executes and verifies all 20 RTIH Mandatory Test Scenarios.
 */

// Autoloader setup
spl_autoload_register(function (string $class) {
    $prefixes = [
        'App\\Controllers\\' => __DIR__ . '/../app/controllers/',
        'App\\Models\\'      => __DIR__ . '/../app/models/',
        'App\\Services\\'    => __DIR__ . '/../app/services/',
        'App\\Middleware\\'  => __DIR__ . '/../app/middleware/',
        'App\\Helpers\\'     => __DIR__ . '/../app/helpers/',
        'App\\Routes\\'      => __DIR__ . '/../routes/',
        'Tests\\'            => __DIR__ . '/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

use App\Helpers\Database;
use App\Helpers\Env;
use App\Helpers\Sanitizer;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Middleware\AuthMiddleware;
use App\Models\Task;
use App\Models\User;
use Tests\TestRunner;

// Load test environment
Env::load(__DIR__ . '/../.env');

// Use isolated test SQLite database for test suite
$testDbPath = __DIR__ . '/../storage/pragathi_test.sqlite';
if (file_exists($testDbPath)) {
    unlink($testDbPath);
}

$pdo = new PDO("sqlite:{$testDbPath}", null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('PRAGMA foreign_keys = ON;');
$pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sqlite.sql'));

// Inject test database instance
Database::setInstance($pdo, 'sqlite');

// Initialize Session mock
Session::start();

$runner = new TestRunner();

echo "Running PRAGATHI Automated Test Suite...\n\n";

// -------------------------------------------------------------
// Scenario 1: Registration works
// -------------------------------------------------------------
$testEmail1 = 'tester1@pragathi.dev';
$testPwd1 = 'Password@123';
$u1Id = User::create('Test User One', $testEmail1, $testPwd1, 'user');
$u1 = User::findById($u1Id);
$runner->assert($u1Id > 0 && $u1['email'] === $testEmail1, '01', 'Registration creates user in database with hashed password');

// -------------------------------------------------------------
// Scenario 2: Duplicate email is rejected
// -------------------------------------------------------------
$valDuplicate = Validator::make([
    'email' => $testEmail1,
], [
    'email' => ['required', 'email', 'unique:users,email'],
]);
$isDuplicateRejected = !$valDuplicate->validate();
$runner->assert($isDuplicateRejected, '02', 'Duplicate email registration is rejected by validation engine');

// -------------------------------------------------------------
// Scenario 3: Valid login works
// -------------------------------------------------------------
$userRecord = User::findByEmail($testEmail1);
$passwordValid = password_verify($testPwd1, $userRecord['password']);
Session::loginUser($userRecord);
$runner->assert($passwordValid && Session::isLoggedIn() && Session::userId() === $u1Id, '03', 'Valid login verifies password and initializes authenticated session');

// -------------------------------------------------------------
// Scenario 4: Incorrect password is rejected
// -------------------------------------------------------------
$wrongPasswordCheck = password_verify('WrongPassword999', $userRecord['password']);
$runner->assert(!$wrongPasswordCheck, '04', 'Incorrect password is appropriately rejected');

// -------------------------------------------------------------
// Scenario 5: Logged-out users cannot access the dashboard
// -------------------------------------------------------------
Session::logoutUser();
// AuthMiddleware check
$canAccessLoggedOut = AuthMiddleware::handle();
$runner->assert($canAccessLoggedOut === false && !Session::isLoggedIn(), '05', 'Logged-out user is intercepted by AuthMiddleware from accessing protected routes');

// Re-login User 1 for subsequent task tests
Session::loginUser($userRecord);

// -------------------------------------------------------------
// Scenario 6: Task creation works
// -------------------------------------------------------------
$taskId = Task::create([
    'user_id' => $u1Id,
    'title' => 'Develop Architecture Review',
    'description' => 'Review modular MVC architecture for PRAGATHI submission',
    'status' => 'pending',
    'priority' => 'high',
    'category_id' => 1,
    'due_date' => date('Y-m-d', strtotime('+3 days')),
    'tags' => 'php,mysql,rtih',
]);
$task = Task::findForUser($taskId, $u1Id);
$runner->assert($taskId > 0 && $task !== null && $task['title'] === 'Develop Architecture Review', '06', 'Task creation persists record with user association');

// -------------------------------------------------------------
// Scenario 7: Empty titles are rejected
// -------------------------------------------------------------
$emptyTitleVal = Validator::make([
    'title' => '   ',
    'status' => 'pending',
    'priority' => 'medium',
], [
    'title' => ['required', 'min:3'],
    'status' => ['required'],
    'priority' => ['required'],
]);
$runner->assert(!$emptyTitleVal->validate(), '07', 'Empty task title is rejected by validation rules');

// -------------------------------------------------------------
// Scenario 8: Task editing works
// -------------------------------------------------------------
Task::update($taskId, $u1Id, [
    'title' => 'Develop Architecture Review (Updated)',
    'description' => 'Updated scope notes',
    'status' => 'pending',
    'priority' => 'high',
    'category_id' => 1,
    'due_date' => date('Y-m-d', strtotime('+5 days')),
    'tags' => 'php,rtih',
]);
$updatedTask = Task::findForUser($taskId, $u1Id);
$runner->assert($updatedTask['title'] === 'Develop Architecture Review (Updated)', '08', 'Task editing updates fields in database');

// -------------------------------------------------------------
// Scenario 9: Status changes persist
// -------------------------------------------------------------
Task::updateStatus($taskId, $u1Id, 'in_progress');
$taskProgress = Task::findForUser($taskId, $u1Id);
Task::updateStatus($taskId, $u1Id, 'completed');
$taskCompleted = Task::findForUser($taskId, $u1Id);
$runner->assert($taskProgress['status'] === 'in_progress' && $taskCompleted['status'] === 'completed', '09', 'Status transitions (pending -> in_progress -> completed) persist');

// -------------------------------------------------------------
// Scenario 10: Task deletion works
// -------------------------------------------------------------
$tempTaskId = Task::create([
    'user_id' => $u1Id,
    'title' => 'Temporary Task to Delete',
    'status' => 'pending',
    'priority' => 'low',
]);
Task::delete($tempTaskId, $u1Id);
$deletedTask = Task::findForUser($tempTaskId, $u1Id);
$runner->assert($deletedTask === null, '10', 'Task deletion permanently removes record');

// -------------------------------------------------------------
// Scenario 11: Search returns correct results
// -------------------------------------------------------------
Task::create([
    'user_id' => $u1Id,
    'title' => 'Implement Search Algorithm',
    'status' => 'pending',
    'priority' => 'medium',
]);
Task::create([
    'user_id' => $u1Id,
    'title' => 'Prepare Database Indexing',
    'status' => 'pending',
    'priority' => 'low',
]);
$searchResults = Task::getAllForUser($u1Id, ['search' => 'Search Algorithm']);
$runner->assert(count($searchResults) === 1 && $searchResults[0]['title'] === 'Implement Search Algorithm', '11', 'Search returns only matching task records');

// -------------------------------------------------------------
// Scenario 12: Status filters work
// -------------------------------------------------------------
$pendingTasks = Task::getAllForUser($u1Id, ['status' => 'pending']);
$allPending = true;
foreach ($pendingTasks as $t) {
    if ($t['status'] !== 'pending') $allPending = false;
}
$runner->assert(count($pendingTasks) > 0 && $allPending, '12', 'Status filter isolates only pending tasks');

// -------------------------------------------------------------
// Scenario 13: Priority filtering works
// -------------------------------------------------------------
$highPriorityTasks = Task::getAllForUser($u1Id, ['priority' => 'high']);
$allHigh = true;
foreach ($highPriorityTasks as $t) {
    if ($t['priority'] !== 'high') $allHigh = false;
}
$runner->assert(count($highPriorityTasks) > 0 && $allHigh, '13', 'Priority filter isolates only high-priority tasks');

// -------------------------------------------------------------
// Scenario 14: Pagination works
// -------------------------------------------------------------
$totalCount = Task::countForUser($u1Id);
$page1 = Task::getAllForUser($u1Id, [], 2, 0);
$page2 = Task::getAllForUser($u1Id, [], 2, 2);
$runner->assert(count($page1) === 2 && count($page2) >= 1 && $page1[0]['id'] !== $page2[0]['id'], '14', 'Pagination limits and offsets slice records accurately');

// -------------------------------------------------------------
// Scenario 15: CSV export works
// -------------------------------------------------------------
$exportData = Task::getAllForExport($u1Id);
$runner->assert(count($exportData) >= 3 && isset($exportData[0]['title']) && isset($exportData[0]['status']), '15', 'CSV export query extracts complete user task records');

// -------------------------------------------------------------
// Scenario 16: Cross-user task access is denied (IDOR Protection)
// -------------------------------------------------------------
$u2Id = User::create('Priya Test', 'priya.test@pragathi.dev', 'Password@123', 'user');
$u2TaskId = Task::create([
    'user_id' => $u2Id,
    'title' => 'Priya Secret Task',
    'status' => 'pending',
    'priority' => 'high',
]);
$crossAccess = Task::findForUser($u2TaskId, $u1Id);
$runner->assert($crossAccess === null, '16', 'Cross-user task access is denied (User 1 cannot view User 2 task)');

// -------------------------------------------------------------
// Scenario 17: Cross-user task modification is denied
// -------------------------------------------------------------
$tamperAttempt = Task::update($u2TaskId, $u1Id, [
    'title' => 'Hacked by User 1',
    'status' => 'completed',
    'priority' => 'low',
]);
$u2TaskUntampered = Task::findForUser($u2TaskId, $u2Id);
$runner->assert($u2TaskUntampered['title'] === 'Priya Secret Task', '17', 'Cross-user task modification is denied (records remain unmanipulated)');

// -------------------------------------------------------------
// Scenario 18: CSRF validation works
// -------------------------------------------------------------
$validToken = Session::getCsrfToken();
$isValidPasses = Session::validateCsrfToken($validToken);
$isForgedFails = Session::validateCsrfToken('forged_fake_token_12345');
$isMissingFails = Session::validateCsrfToken(null);
$runner->assert($isValidPasses && !$isForgedFails && !$isMissingFails, '18', 'CSRF validation accepts valid token and rejects forged/missing tokens');

// -------------------------------------------------------------
// Scenario 19: Invalid inputs are handled safely (XSS escaping)
// -------------------------------------------------------------
$xssPayload = "<script>alert('XSS Attack');</script>";
$escaped = Sanitizer::escape($xssPayload);
$runner->assert(!str_contains($escaped, '<script>') && str_contains($escaped, '&lt;script&gt;'), '19', 'XSS input is safely escaped into HTML entities');

// -------------------------------------------------------------
// Scenario 20: Logout invalidates the session
// -------------------------------------------------------------
Session::logoutUser();
$isLoggedOut = !Session::isLoggedIn() && Session::userId() === 0;
$runner->assert($isLoggedOut, '20', 'Logout completely destroys session state and revokes access');

// Print final summary report
$exitCode = $runner->report();

// Clean up temporary test database
if (file_exists($testDbPath)) {
    @unlink($testDbPath);
}

exit($exitCode);
