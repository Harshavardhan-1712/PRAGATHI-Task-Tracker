<div class="row justify-content-center py-5">
    <div class="col-12 col-md-6 text-center">
        <div class="display-1 fw-bold text-danger mb-2">403</div>
        <h3 class="fw-bold mb-3">Access Forbidden</h3>
        <p class="text-muted mb-4">
            <?= htmlspecialchars($message ?? 'You do not have authorization to access or modify this resource. Task ownership isolation is strictly enforced.', ENT_QUOTES) ?>
        </p>
        <a href="/dashboard" class="btn btn-primary px-4">
            <i class="bi bi-house me-1"></i> Return to Workspace
        </a>
    </div>
</div>
