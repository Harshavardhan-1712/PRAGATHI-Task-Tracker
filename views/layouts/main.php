<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= htmlspecialchars($currentUser['theme'] ?? 'light', ENT_QUOTES) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Helpers\View::csrfToken() ?>">
    <title><?= htmlspecialchars($pageTitle ?? 'PRAGATHI', ENT_QUOTES) ?></title>

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
    
    <!-- PRAGATHI SaaS Custom Theme -->
    <link rel="stylesheet" href="/assets/css/pragathi.css">
</head>
<body>

<div class="app-wrapper">
    <!-- Sidebar Navigation -->
    <aside class="app-sidebar" id="appSidebar">
        <a href="/dashboard" class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-compass"></i>
            </div>
            <div>
                <h1 class="brand-title">PRAGATHI</h1>
                <p class="brand-subtitle">Productivity OS</p>
            </div>
        </a>

        <div class="sidebar-nav">
            <div class="nav-section-title">Core Workspace</div>
            <a href="/dashboard" class="nav-link <?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Overview</span>
            </a>
            <a href="/tasks" class="nav-link <?= ($activeNav ?? '') === 'tasks' ? 'active' : '' ?>">
                <i class="bi bi-check2-square"></i>
                <span>My Tasks</span>
            </a>
            <a href="/kanban" class="nav-link <?= ($activeNav ?? '') === 'kanban' ? 'active' : '' ?>">
                <i class="bi bi-kanban"></i>
                <span>Kanban Board</span>
            </a>
            <a href="/calendar" class="nav-link <?= ($activeNav ?? '') === 'calendar' ? 'active' : '' ?>">
                <i class="bi bi-calendar3"></i>
                <span>Calendar</span>
            </a>

            <div class="nav-section-title">Insights & History</div>
            <a href="/activity" class="nav-link <?= ($activeNav ?? '') === 'activity' ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i>
                <span>Activity Timeline</span>
            </a>
            <a href="/notifications" class="nav-link <?= ($activeNav ?? '') === 'notifications' ? 'active' : '' ?>">
                <i class="bi bi-bell"></i>
                <span>Notifications</span>
                <?php if (!empty($unreadNotificationCount)): ?>
                    <span class="badge bg-danger rounded-pill ms-auto"><?= (int)$unreadNotificationCount ?></span>
                <?php endif; ?>
            </a>
            <a href="/tasks/archived" class="nav-link">
                <i class="bi bi-archive"></i>
                <span>Archived Tasks</span>
            </a>

            <div class="nav-section-title">Preferences</div>
            <a href="/settings" class="nav-link <?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-weight: 600; font-size: 0.85rem;">
                        <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="text-truncate">
                        <div class="text-white small fw-semibold text-truncate"><?= htmlspecialchars($currentUser['name'] ?? 'User', ENT_QUOTES) ?></div>
                        <div class="text-muted small" style="font-size: 0.7rem;"><?= htmlspecialchars($currentUser['email'] ?? '', ENT_QUOTES) ?></div>
                    </div>
                </div>
                <form action="/logout" method="POST" class="m-0">
                    <?= \App\Helpers\View::csrfField() ?>
                    <button type="submit" class="btn btn-sm btn-outline-secondary text-white border-0" title="Sign Out">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="app-main">
        <!-- Sticky Topbar -->
        <header class="app-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggleBtn" aria-label="Toggle Menu">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-sm-block text-muted small fst-italic">
                    "Turn Intentions Into Accomplishments"
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Quick Create Button -->
                <a href="/tasks/create" class="btn btn-sm btn-primary d-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-plus-lg"></i>
                    <span class="d-none d-sm-inline">New Task</span>
                </a>

                <!-- Notification Bell -->
                <a href="/notifications" class="btn btn-sm btn-outline-secondary position-relative rounded-circle" style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-bell"></i>
                    <?php if (!empty($unreadNotificationCount)): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            <?= (int)$unreadNotificationCount ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Dark / Light Theme Toggle -->
                <button class="btn btn-sm btn-outline-secondary theme-toggle-btn rounded-circle" style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;" title="Toggle Dark/Light Mode">
                    <i class="bi bi-moon-stars-fill theme-toggle-icon"></i>
                </button>
            </div>
        </header>

        <!-- Page Body Content -->
        <main class="app-content">
            <?= $content ?>
        </main>
    </div>
</div>

<!-- Floating Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <?php
    $flashes = \App\Helpers\Session::getAllFlashes();
    foreach ($flashes as $type => $messages):
        $bgClass = match($type) {
            'success' => 'text-bg-success',
            'error'   => 'text-bg-danger',
            'warning' => 'text-bg-warning',
            default   => 'text-bg-info'
        };
        $iconClass = match($type) {
            'success' => 'bi-check-circle-fill',
            'error'   => 'bi-exclamation-triangle-fill',
            'warning' => 'bi-exclamation-circle-fill',
            default   => 'bi-info-circle-fill'
        };
        foreach ($messages as $msg):
    ?>
        <div class="toast align-items-center <?= $bgClass ?> border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi <?= $iconClass ?> fs-5"></i>
                    <div><?= htmlspecialchars($msg, ENT_QUOTES) ?></div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php 
        endforeach;
    endforeach; 
    ?>
</div>

<!-- Scripts -->
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/chart.umd.min.js"></script>
<script src="/assets/js/pragathi.js"></script>
<?php if (($activeNav ?? '') === 'kanban'): ?>
    <script src="/assets/js/kanban.js"></script>
<?php endif; ?>
</body>
</html>
