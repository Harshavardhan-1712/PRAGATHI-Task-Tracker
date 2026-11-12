<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">My Tasks</h2>
        <p class="text-muted small mb-0">Manage, organize, and execute your objectives.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="/tasks/export?<?= http_build_query($params) ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2" title="Export filtered tasks as CSV">
            <i class="bi bi-download"></i>
            <span>Export CSV</span>
        </a>
        <a href="/tasks/create" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-plus-lg"></i>
            <span>Create Task</span>
        </a>
    </div>
</div>

<!-- Search & Filtering Toolbar -->
<div class="card mb-4">
    <div class="card-body">
        <form action="/tasks" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="Search by title or description..." value="<?= htmlspecialchars($params['search'] ?? '', ENT_QUOTES) ?>">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= ($params['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="in_progress" <?= ($params['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed" <?= ($params['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select" name="priority" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="high" <?= ($params['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="medium" <?= ($params['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="low" <?= ($params['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select" name="category_id" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= ((string)($params['category_id'] ?? '')) === ((string)$cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select" name="sort" onchange="this.form.submit()">
                    <option value="due_date_asc" <?= ($params['sort'] ?? '') === 'due_date_asc' ? 'selected' : '' ?>>Due Date (Earliest)</option>
                    <option value="due_date_desc" <?= ($params['sort'] ?? '') === 'due_date_desc' ? 'selected' : '' ?>>Due Date (Latest)</option>
                    <option value="priority_desc" <?= ($params['sort'] ?? '') === 'priority_desc' ? 'selected' : '' ?>>Priority (High &rarr; Low)</option>
                    <option value="created_at_desc" <?= ($params['sort'] ?? '') === 'created_at_desc' ? 'selected' : '' ?>>Recently Added</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Toolbar (Displayed when tasks selected) -->
<form action="/tasks/bulk" method="POST" id="bulkForm">
    <?= \App\Helpers\View::csrfField() ?>
    
    <div class="alert alert-primary d-flex align-items-center justify-content-between py-2 px-3 mb-3 d-none shadow-sm" id="bulkActionBar">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check2-all fs-5"></i>
            <span><strong id="selectedCount">0</strong> task(s) selected</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <select name="bulk_action" class="form-select form-select-sm" style="width: auto;" required>
                <option value="" disabled selected>Select Action...</option>
                <option value="status_pending">Mark as Pending</option>
                <option value="status_in_progress">Mark as In Progress</option>
                <option value="status_completed">Mark as Completed</option>
                <option value="archive">Archive Selected</option>
                <option value="delete">Permanently Delete</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Apply bulk action to selected tasks?')">Apply</button>
        </div>
    </div>

    <!-- Tasks List Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($tasks)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 text-secondary mb-2 d-block"></i>
                    <h5 class="fw-semibold">No tasks found</h5>
                    <p class="small text-muted mb-3">Try adjusting your filters or search keywords, or create a new task.</p>
                    <a href="/tasks/create" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Create First Task
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th style="width: 40px;" class="ps-3">
                                    <input type="checkbox" class="form-check-input" id="selectAllTasks" title="Select All">
                                </th>
                                <th>Task Title</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td class="ps-3">
                                        <input type="checkbox" class="form-check-input task-checkbox" name="task_ids[]" value="<?= (int)$task['id'] ?>">
                                    </td>
                                    <td>
                                        <a href="/tasks/<?= (int)$task['id'] ?>/edit" class="text-decoration-none fw-semibold text-reset d-block">
                                            <?= htmlspecialchars($task['title'], ENT_QUOTES) ?>
                                        </a>
                                        <?php if (!empty($task['description'])): ?>
                                            <div class="text-muted small text-truncate" style="max-width: 320px;">
                                                <?= htmlspecialchars($task['description'], ENT_QUOTES) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($task['tags'])): ?>
                                            <div class="mt-1">
                                                <?php foreach (explode(',', $task['tags']) as $tag): ?>
                                                    <span class="badge bg-light text-secondary border me-1" style="font-size: 0.65rem;">#<?= htmlspecialchars(trim($tag), ENT_QUOTES) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($task['category_name'])): ?>
                                            <span class="badge" style="background-color: <?= htmlspecialchars($task['category_color'] ?? '#6366f1', ENT_QUOTES) ?>;">
                                                <?= htmlspecialchars($task['category_name'], ENT_QUOTES) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge-priority priority-<?= $task['priority'] ?>">
                                            <?= ucfirst($task['priority']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status status-<?= $task['status'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($task['due_date'])): ?>
                                            <div class="small fw-semibold"><?= \App\Helpers\Sanitizer::formatDate($task['due_date']) ?></div>
                                            <div class="text-muted" style="font-size: 0.72rem;">
                                                <?= \App\Helpers\Sanitizer::overdueText($task['due_date']) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">No due date</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="/tasks/<?= (int)$task['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit Task">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-secondary" title="Archive Task" onclick="submitSingleAction('/tasks/<?= (int)$task['id'] ?>/archive', 'Archive this task?')">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" title="Delete Task" onclick="submitSingleAction('/tasks/<?= (int)$task['id'] ?>/delete', 'Permanently delete this task?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer d-flex align-items-center justify-content-between py-3">
                        <div class="small text-muted">
                            Showing <?= count($tasks) ?> of <?= $totalTasks ?> total tasks
                        </div>
                        <nav aria-label="Tasks pagination">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="/tasks?<?= http_build_query(array_merge($params, ['page' => $page - 1])) ?>">Previous</a>
                                </li>
                                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="/tasks?<?= http_build_query(array_merge($params, ['page' => $p])) ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="/tasks?<?= http_build_query(array_merge($params, ['page' => $page + 1])) ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Hidden Single Action Form -->
<form id="singleActionForm" method="POST" style="display: none;">
    <?= \App\Helpers\View::csrfField() ?>
</form>

<script>
function submitSingleAction(url, confirmPrompt) {
    if (confirm(confirmPrompt)) {
        const form = document.getElementById('singleActionForm');
        form.action = url;
        form.submit();
    }
}
</script>
