<form action="/login" method="POST" class="needs-validation" novalidate>
    <?= \App\Helpers\View::csrfField() ?>

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" id="email" name="email" placeholder="name@company.com" required autofocus>
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label small fw-semibold">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
        Sign In to Workspace
    </button>

    <div class="text-center small text-muted mb-4">
        Don't have an account? <a href="/register" class="text-decoration-none fw-semibold">Create an account</a>
    </div>

    <!-- Quick Demo Accounts for Evaluators -->
    <div class="p-3 bg-light rounded-3 border">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-secondary-subtle text-secondary border">RTIH Assessment Evaluator</span>
            <span class="small text-muted">Quick Fill</span>
        </div>
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary text-start" onclick="fillDemo('arjun@pragathi.dev', 'Pragathi@2026')">
                <i class="bi bi-person-check me-1"></i> <strong>Arjun Varma</strong> (arjun@pragathi.dev)
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="fillDemo('priya@pragathi.dev', 'Pragathi@2026')">
                <i class="bi bi-person me-1"></i> <strong>Priya Sharma</strong> (priya@pragathi.dev)
            </button>
        </div>
    </div>
</form>

<script>
function fillDemo(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
