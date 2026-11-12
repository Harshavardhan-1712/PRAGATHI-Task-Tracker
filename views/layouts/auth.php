<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Helpers\View::csrfToken() ?>">
    <title><?= htmlspecialchars($pageTitle ?? 'PRAGATHI', ENT_QUOTES) ?></title>

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
    
    <!-- PRAGATHI Stylesheet -->
    <link rel="stylesheet" href="/assets/css/pragathi.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-box">
        <div class="text-center mb-4">
            <div class="brand-icon mx-auto mb-3" style="width: 48px; height: 48px; font-size: 1.5rem;">
                <i class="bi bi-compass"></i>
            </div>
            <h2 class="fw-bold mb-1 tracking-tight">PRAGATHI</h2>
            <p class="text-muted small">Turn Intentions Into Accomplishments</p>
        </div>

        <?= $content ?>
    </div>
</div>

<!-- Floating Toast Alerts -->
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

<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/pragathi.js"></script>
</body>
</html>
