# PRAGATHI — EXECUTIVE TECHNICAL SUMMARY
**Candidate Technical Submission — IT Internship Selection Process**
**Ratan Tata Innovation Hub (RTIH), Amaravati**

---

## 1. Project At A Glance
- **Platform Name:** PRAGATHI
- **Tagline:** Turn Intentions Into Accomplishments
- **Core Philosophy:** A focused, high-density productivity management system combining rigorous backend security with intuitive SaaS UX.
- **Tech Stack:** Core PHP 8.2+, MySQL 8.0/MariaDB via PDO, ES6+ JavaScript, Bootstrap 5.3, Chart.js.
- **Frameworks Used:** **None.** (Pure Core PHP adhering strictly to technical instructions).

---

## 2. Fulfillment of Mandatory Requirements

### Level 1: Basic Task Management
- [x] Create, display, edit, and delete tasks.
- [x] Fields: `id`, `user_id`, `category_id`, `title`, `description`, `status`, `priority`, `due_date`, `tags`, `created_at`, `updated_at`, `archived_at`.
- [x] Deletion protected by modal / JavaScript confirmation prompts.
- [x] Flash notification system (`Session::setFlash`, `Session::getAllFlashes`) displaying success and error toasts.

### Level 2: Status, Search, and Validation
- [x] Status values: `pending`, `in_progress`, `completed`.
- [x] Search across task titles and descriptions.
- [x] Multi-criteria filtering by Status, Priority, and Category.
- [x] Client-side HTML5 form validation + robust backend validator (`Validator.php`).
- [x] Friendly validation error messages returned upon any malformed submission.

### Level 3: Login, Authorization, and Security
- [x] Registration and authentication system.
- [x] Password hashing using `password_hash(..., PASSWORD_BCRYPT)`.
- [x] Constant-time password verification using `password_verify()`.
- [x] Hardened PHP session management (`httponly`, `samesite=Lax`, strict mode).
- [x] Session fixation defense via `session_regenerate_id(true)`.
- [x] Brute-force throttling locking accounts for 60 seconds after 5 consecutive failures.
- [x] User-specific task ownership isolation (IDOR protection) on all CRUD and bulk actions.
- [x] 100% PDO prepared statements with parameter binding.
- [x] XSS protection via `htmlspecialchars` output escaping.
- [x] Cryptographic CSRF protection with tokens checked on all state-changing requests.

---

## 3. PRAGATHI Product Differentiators

1. **Productivity Overview Dashboard:**
   - Real-time aggregation of active tasks, completion rate (%), and overdue items.
   - Interactive Chart.js 7-day velocity line chart and category breakdown doughnut chart.
   - Dynamic upcoming deadlines table and live activity feed.
2. **Interactive Kanban Board:**
   - HTML5 native drag-and-drop across 3 workflow columns.
   - Asynchronous status persistence via Fetch API with CSRF token validation.
   - Optimistic UI updates with automatic rollback on network failure.
3. **Monthly Visual Calendar:**
   - Chronological monthly schedule view with today highlight.
   - Color-coded category task chips plotted on due dates.
4. **Chronological Activity Timeline:**
   - Database-backed `activity_logs` table tracking user operations.
   - Human-readable relative timestamps ("10m ago", "Yesterday").
5. **In-App Notification Center:**
   - Dynamic deadline alerts and overdue warnings.
   - Unread badge counter, mark as read, and mark all as read.
6. **Advanced Operations:**
   - Multi-task checkbox selection with bulk status update, archive, and permanent delete.
   - CSV export formatted with UTF-8 BOM for Microsoft Excel compatibility.
   - Light / Dark SaaS theme switcher with persistence.

---

## 4. Test Verification Status
All 20 required verification scenarios pass 100% on the automated test suite:
```text
Test Runner: tests/run_tests.php
Result: 20 passed, 0 failed (100% PASS)
```

---

## 5. Ready-To-Test Demo Accounts
- **Arjun Varma:** `arjun@pragathi.dev` / `Pragathi@2026`
- **Priya Sharma:** `priya@pragathi.dev` / `Pragathi@2026`
