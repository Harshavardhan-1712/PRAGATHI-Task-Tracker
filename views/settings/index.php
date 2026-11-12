<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Account & Workspace Settings</h2>
        <p class="text-muted small mb-0">Configure your profile, security credentials, and custom taxonomy.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Profile & Preferences -->
    <div class="col-12 col-lg-6">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-person-gear text-primary"></i>
                <span>Profile & Display Preferences</span>
            </div>
            <div class="card-body p-4">
                <form action="/settings/profile" method="POST">
                    <?= \App\Helpers\View::csrfField() ?>

                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">Email Address</label>
                        <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES) ?>" disabled>
                        <div class="form-text">Your primary login identifier cannot be altered.</div>
                    </div>

                    <div class="mb-4">
                        <label for="theme_preference" class="form-label small fw-semibold">Default Theme Preference</label>
                        <select class="form-select" id="theme_preference" name="theme_preference">
                            <option value="light" <?= ($user['theme_preference'] ?? 'light') === 'light' ? 'selected' : '' ?>>Light Theme</option>
                            <option value="dark" <?= ($user['theme_preference'] ?? 'light') === 'dark' ? 'selected' : '' ?>>Dark Theme (OLED Friendly)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary px-4">Save Profile Changes</button>
                </form>
            </div>
        </div>

        <!-- Security / Password Update -->
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock text-danger"></i>
                <span>Security & Password Management</span>
            </div>
            <div class="card-body p-4">
                <form action="/settings/password" method="POST">
                    <?= \App\Helpers\View::csrfField() ?>

                    <div class="mb-3">
                        <label for="current_password" class="form-label small fw-semibold">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="••••••••">
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label small fw-semibold">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8" placeholder="Minimum 8 characters">
                    </div>

                    <div class="mb-4">
                        <label for="new_password_confirmation" class="form-label small fw-semibold">Confirm New Password</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required minlength="8" placeholder="Repeat new password">
                    </div>

                    <button type="submit" class="btn btn-outline-danger px-4">Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Category Taxonomy Management -->
    <div class="col-12 col-lg-6">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-tags text-info"></i>
                    <span>Category Taxonomy</span>
                </div>
                <span class="badge bg-secondary-subtle text-secondary border">Organize Work</span>
            </div>
            <div class="card-body p-4">
                <!-- Add Category Form -->
                <form action="/settings/category" method="POST" class="mb-4 p-3 bg-light rounded-3 border">
                    <?= \App\Helpers\View::csrfField() ?>
                    <h6 class="fw-bold mb-3 small text-uppercase">Create Custom Category</h6>
                    <div class="row g-2 align-items-center">
                        <div class="col-6">
                            <input type="text" class="form-control form-control-sm" name="name" placeholder="Category Name" required maxlength="50">
                        </div>
                        <div class="col-3">
                            <input type="color" class="form-control form-control-sm form-control-color w-100" name="color" value="#6366f1" title="Choose color">
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Add</button>
                        </div>
                    </div>
                </form>

                <!-- Existing Categories List -->
                <h6 class="fw-bold mb-2 small text-uppercase text-muted">Available Categories</h6>
                <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                    <?php foreach ($categories as $cat): ?>
                        <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle" style="width: 12px; height: 12px; background-color: <?= htmlspecialchars($cat['color'] ?? '#6366f1', ENT_QUOTES) ?>;"></span>
                                <span class="fw-semibold small"><?= htmlspecialchars($cat['name'], ENT_QUOTES) ?></span>
                                <?php if ($cat['user_id'] === null): ?>
                                    <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">System Default</span>
                                <?php else: ?>
                                    <span class="badge bg-info-subtle text-info border" style="font-size: 0.65rem;">Custom</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($cat['user_id'] !== null): ?>
                                <form action="/settings/category/<?= (int)$cat['id'] ?>/delete" method="POST" class="m-0" onsubmit="return confirm('Delete this custom category?');">
                                    <?= \App\Helpers\View::csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- System & Assignment Information -->
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-info-circle text-primary"></i>
                <span>Technical Submission Context</span>
            </div>
            <div class="card-body p-4 small">
                <p class="mb-2"><strong>Project:</strong> PRAGATHI — Productivity Management Platform</p>
                <p class="mb-2"><strong>Organization:</strong> Ratan Tata Innovation Hub (RTIH), Amaravati</p>
                <p class="mb-2"><strong>Assessment Scope:</strong> Levels 1, 2, and 3 + Advanced Differentiators</p>
                <p class="mb-2"><strong>Architecture:</strong> Core PHP 8.2+, MySQL 8.0/MariaDB PDO, Vanilla JS ES6+</p>
                <p class="mb-0"><strong>Database Driver:</strong> <code><?= \App\Helpers\Database::getDriver() ?></code></p>
            </div>
        </div>
    </div>
</div>
