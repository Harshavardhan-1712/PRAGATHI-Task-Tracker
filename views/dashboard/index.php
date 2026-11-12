<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Productivity Overview</h2>
        <p class="text-muted small mb-0">Welcome back, <strong><?= htmlspecialchars($currentUser['name'] ?? '', ENT_QUOTES) ?></strong>. Here is your execution status today.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#quickCreateModal">
            <i class="bi bi-plus-circle"></i>
            <span>Quick Task</span>
        </button>
        <a href="/kanban" class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <i class="bi bi-kanban"></i>
            <span>Open Board</span>
        </a>
    </div>
</div>

<!-- Metric Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label">Total Tasks</div>
            <div class="metric-value text-primary"><?= (int)$metrics['total'] ?></div>
            <div class="text-muted small mt-1"><i class="bi bi-layers"></i> In workspace</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label">In Progress</div>
            <div class="metric-value text-info"><?= (int)$metrics['in_progress'] ?></div>
            <div class="text-muted small mt-1"><i class="bi bi-arrow-repeat"></i> Active now</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label">Pending</div>
            <div class="metric-value text-warning"><?= (int)$metrics['pending'] ?></div>
            <div class="text-muted small mt-1"><i class="bi bi-hourglass-split"></i> In queue</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label">Completed</div>
            <div class="metric-value text-success"><?= (int)$metrics['completed'] ?></div>
            <div class="text-muted small mt-1"><i class="bi bi-check2-circle"></i> Accomplished</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label">Overdue</div>
            <div class="metric-value text-danger"><?= (int)$metrics['overdue'] ?></div>
            <div class="text-muted small mt-1"><i class="bi bi-exclamation-octagon"></i> Needs focus</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label">Completion</div>
            <div class="metric-value text-success"><?= $metrics['completion_rate'] ?>%</div>
            <div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $metrics['completion_rate'] ?>%;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <!-- 7-Day Completion Velocity Chart -->
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-graph-up text-primary"></i>
                    <span>Productivity Velocity (Last 7 Days)</span>
                </div>
                <span class="badge bg-primary-subtle text-primary border">Daily Completed Tasks</span>
            </div>
            <div class="card-body">
                <canvas id="velocityChart" height="110"></canvas>
            </div>
        </div>
    </div>

    <!-- Category Breakdown Doughnut -->
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-pie-chart text-info"></i>
                    <span>Category Distribution</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="categoryChart" style="max-height: 220px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Deadlines & Recent Activity Row -->
<div class="row g-4">
    <!-- Upcoming Deadlines -->
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-alarm text-warning"></i>
                    <span>Upcoming Deadlines</span>
                </div>
                <a href="/tasks" class="small text-decoration-none fw-semibold">View All Tasks &rarr;</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcomingDeadlines)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-check fs-1 text-secondary mb-2 d-block"></i>
                        <p class="mb-0">No impending deadlines! You are all caught up.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th class="ps-3">Task Title</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingDeadlines as $t): ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold text-truncate" style="max-width: 220px;">
                                            <a href="/tasks/<?= (int)$t['id'] ?>/edit" class="text-decoration-none text-reset">
                                                <?= htmlspecialchars($t['title'], ENT_QUOTES) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if (!empty($t['category_name'])): ?>
                                                <span class="badge" style="background-color: <?= htmlspecialchars($t['category_color'] ?? '#6366f1', ENT_QUOTES) ?>;">
                                                    <?= htmlspecialchars($t['category_name'], ENT_QUOTES) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge-priority priority-<?= $t['priority'] ?>">
                                                <?= ucfirst($t['priority']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold"><?= \App\Helpers\Sanitizer::formatDate($t['due_date']) ?></div>
                                            <div class="text-muted" style="font-size: 0.72rem;"><?= \App\Helpers\Sanitizer::overdueText($t['due_date']) ?></div>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="/tasks/<?= (int)$t['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Activity Timeline -->
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    <span>Recent Activity</span>
                </div>
                <a href="/activity" class="small text-decoration-none fw-semibold">View Log &rarr;</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivities)): ?>
                    <div class="text-center py-4 text-muted small">
                        No recent activity recorded yet.
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($recentActivities as $act): ?>
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <?php
                                    $icon = match($act['action_type']) {
                                        'created' => 'bi-plus-circle',
                                        'completed' => 'bi-check2',
                                        'status_changed' => 'bi-arrow-left-right',
                                        'deleted' => 'bi-trash',
                                        'archived' => 'bi-archive',
                                        default => 'bi-pencil'
                                    };
                                    ?>
                                    <i class="bi <?= $icon ?>"></i>
                                </div>
                                <div>
                                    <div class="small fw-semibold text-truncate"><?= htmlspecialchars($act['description'], ENT_QUOTES) ?></div>
                                    <div class="text-muted" style="font-size: 0.72rem;">
                                        <?= \App\Helpers\Sanitizer::relativeTime($act['created_at']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Task Creation Modal -->
<div class="modal fade" id="quickCreateModal" tabindex="-1" aria-labelledby="quickCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/tasks" method="POST" class="modal-content">
            <?= \App\Helpers\View::csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="quickCreateModalLabel">Quick Task Creation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="quickTitle" class="form-label small fw-semibold">Task Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="quickTitle" name="title" required placeholder="What needs to be accomplished?" autofocus>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="quickPriority" class="form-label small fw-semibold">Priority</label>
                        <select class="form-select" id="quickPriority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="quickStatus" class="form-label small fw-semibold">Status</label>
                        <select class="form-select" id="quickStatus" name="status">
                            <option value="pending" selected>Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="quickCategory" class="form-label small fw-semibold">Category</label>
                        <select class="form-select" id="quickCategory" name="category_id">
                            <option value="">None (General)</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="quickDueDate" class="form-label small fw-semibold">Due Date</label>
                        <input type="date" class="form-control" id="quickDueDate" name="due_date" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="mb-2">
                    <label for="quickDescription" class="form-label small fw-semibold">Description</label>
                    <textarea class="form-control" id="quickDescription" name="description" rows="2" placeholder="Optional context or instructions..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Chart.js Scripts Initialization -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Velocity Trend Chart
    const velocityCtx = document.getElementById('velocityChart').getContext('2d');
    new Chart(velocityCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($metrics['weekly_trend']['labels']) ?>,
            datasets: [{
                label: 'Completed Tasks',
                data: <?= json_encode($metrics['weekly_trend']['values']) ?>,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.3,
                borderWidth: 2,
                pointBackgroundColor: '#4f46e5',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    // 2. Category Breakdown Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($metrics['category_breakdown']['labels']) ?>,
            datasets: [{
                data: <?= json_encode($metrics['category_breakdown']['values']) ?>,
                backgroundColor: <?= json_encode($metrics['category_breakdown']['colors']) ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
});
</script>
