<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold mb-1">Create New Task</h2>
                <p class="text-muted small mb-0">Define your objective with clear milestones and deadlines.</p>
            </div>
            <a href="/tasks" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Tasks
            </a>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <form action="/tasks" method="POST" class="needs-validation" novalidate>
                    <?= \App\Helpers\View::csrfField() ?>

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required minlength="3" maxlength="255" placeholder="e.g., Deliver RTIH Technical Assessment Report" autofocus>
                        <div class="form-text">Give your task a clear, concise, and actionable title.</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Detail any specifications, notes, reference URLs or checklist items..."></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" selected>Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="priority" class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="category_id" class="form-label fw-semibold">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">None (General)</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="due_date" class="form-label fw-semibold">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?= date('Y-m-d') ?>">
                            <div class="form-text">Deadlines power calendar views and proactive alerts.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tags" class="form-label fw-semibold">Tags (comma-separated)</label>
                            <input type="text" class="form-control" id="tags" name="tags" placeholder="e.g., rtih, php, urgent">
                            <div class="form-text">Keywords to quickly categorize and search.</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                        <a href="/tasks" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
