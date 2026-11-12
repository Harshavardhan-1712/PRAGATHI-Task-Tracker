<div class="row justify-content-center py-5">
    <div class="col-12 col-md-8 text-center">
        <div class="display-1 fw-bold text-warning mb-2">500</div>
        <h3 class="fw-bold mb-3">Internal Server Error</h3>
        <p class="text-muted mb-4">
            <?= htmlspecialchars($error ?? 'An unexpected exception occurred during execution.', ENT_QUOTES) ?>
        </p>

        <?php if (!empty($file) && !empty($trace)): ?>
            <div class="card text-start bg-light mb-4">
                <div class="card-header small fw-bold text-danger">Debug Details (Development Mode)</div>
                <div class="card-body">
                    <div class="small fw-semibold mb-2">File: <?= htmlspecialchars($file, ENT_QUOTES) ?>:<?= (int)$line ?></div>
                    <pre class="small mb-0" style="max-height: 200px; overflow-y: auto;"><code><?= htmlspecialchars($trace, ENT_QUOTES) ?></code></pre>
                </div>
            </div>
        <?php endif; ?>

        <a href="/dashboard" class="btn btn-primary px-4">
            <i class="bi bi-house me-1"></i> Return to Workspace
        </a>
    </div>
</div>
