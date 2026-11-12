<form action="/register" method="POST">
    <?= \App\Helpers\View::csrfField() ?>

    <div class="mb-3">
        <label for="name" class="form-label small fw-semibold">Full Name</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" id="name" name="name" placeholder="John Doe" required autofocus>
        </div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" id="email" name="email" placeholder="name@company.com" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label small fw-semibold">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" placeholder="At least 8 characters" required minlength="8">
        </div>
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label small fw-semibold">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Re-enter password" required minlength="8">
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
        Create My Account
    </button>

    <div class="text-center small text-muted">
        Already have an account? <a href="/login" class="text-decoration-none fw-semibold">Sign in here</a>
    </div>
</form>
