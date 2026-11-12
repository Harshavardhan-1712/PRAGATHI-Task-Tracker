<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold mb-1">Edit Task</h2>
                <p class="text-muted small mb-0">Modify task parameters and execution status.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="/tasks" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body p-4">
                <form action="/tasks/<?= (int)$task['id'] ?>/update" method="POST">
                    <?= \App\Helpers\View::csrfField() ?>

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required minlength="3" maxlength="255" value="<?= htmlspecialchars($task['title'], ENT_QUOTES) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($task['description'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="priority" class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="low" <?= $task['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= $task['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= $task['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="category_id" class="form-label fw-semibold">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">None (General)</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>" <?= ((string)$task['category_id']) === ((string)$cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="due_date" class="form-label fw-semibold">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?= htmlspecialchars($task['due_date'] ?? '', ENT_QUOTES) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tags" class="form-label fw-semibold">Tags (comma-separated)</label>
                            <input type="text" class="form-control" id="tags" name="tags" value="<?= htmlspecialchars($task['tags'] ?? '', ENT_QUOTES) ?>">
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <div class="text-muted small">
                            Created: <?= \App\Helpers\Sanitizer::formatDate($task['created_at']) ?>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="/tasks" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danger Zone Card -->
        <div class="card border-danger-subtle">
            <div class="card-header bg-danger-subtle text-danger fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle"></i>
                <span>Task Lifecycle & Danger Zone</span>
            </div>
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-semibold small">Archive or Permanently Delete</div>
                    <div class="text-muted small">Archived tasks can be restored anytime. Deletion is irreversible.</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form action="/tasks/<?= (int)$task['id'] ?>/archive" method="POST" onsubmit="return confirm('Archive this task?');">
                        <?= \App\Helpers\View::csrfField() ?>
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-archive me-1"></i> Archive
                        </button>
                    </form>
                    <form action="/tasks/<?= (int)$task['id'] ?>/delete" method="POST" onsubmit="return confirm('Permanently delete this task? This cannot be undone.');">
                        <?= \App\Helpers\View::csrfField() ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
