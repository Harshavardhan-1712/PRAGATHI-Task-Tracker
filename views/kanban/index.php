<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Interactive Kanban Board</h2>
        <p class="text-muted small mb-0">Drag and drop cards across columns to update task workflow states in real-time.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="/tasks/create" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i>
            <span>Add Task</span>
        </a>
    </div>
</div>

<div class="kanban-board">
    <!-- Column: Pending -->
    <div class="kanban-column" id="column-pending">
        <div class="kanban-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-hourglass-split text-warning"></i>
                <span class="fw-bold">Pending</span>
            </div>
            <span class="badge bg-warning-subtle text-warning border kanban-count">
                <?= count($boardTasks['pending'] ?? []) ?>
            </span>
        </div>
        <div class="kanban-tasks" data-status="pending">
            <?php foreach (($boardTasks['pending'] ?? []) as $task): ?>
                <div class="kanban-card" draggable="true" data-task-id="<?= (int)$task['id'] ?>">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <?php if (!empty($task['category_name'])): ?>
                            <span class="badge" style="background-color: <?= htmlspecialchars($task['category_color'] ?? '#6366f1', ENT_QUOTES) ?>; font-size: 0.65rem;">
                                <?= htmlspecialchars($task['category_name'], ENT_QUOTES) ?>
                            </span>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                        <span class="badge-priority priority-<?= $task['priority'] ?>">
                            <?= ucfirst($task['priority']) ?>
                        </span>
                    </div>

                    <a href="/tasks/<?= (int)$task['id'] ?>/edit" class="text-decoration-none text-reset fw-semibold d-block mb-2">
                        <?= htmlspecialchars($task['title'], ENT_QUOTES) ?>
                    </a>

                    <?php if (!empty($task['description'])): ?>
                        <div class="text-muted small text-truncate mb-2" style="font-size: 0.78rem;">
                            <?= htmlspecialchars($task['description'], ENT_QUOTES) ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top text-muted small" style="font-size: 0.72rem;">
                        <div>
                            <?php if (!empty($task['due_date'])): ?>
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= \App\Helpers\Sanitizer::formatDate($task['due_date'], 'M d') ?>
                            <?php else: ?>
                                <span>No date</span>
                            <?php endif; ?>
                        </div>
                        <a href="/tasks/<?= (int)$task['id'] ?>/edit" class="text-muted text-decoration-none">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Column: In Progress -->
    <div class="kanban-column" id="column-in-progress">
        <div class="kanban-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-arrow-repeat text-info"></i>
                <span class="fw-bold">In Progress</span>
            </div>
            <span class="badge bg-info-subtle text-info border kanban-count">
                <?= count($boardTasks['in_progress'] ?? []) ?>
            </span>
        </div>
        <div class="kanban-tasks" data-status="in_progress">
            <?php foreach (($boardTasks['in_progress'] ?? []) as $task): ?>
                <div class="kanban-card" draggable="true" data-task-id="<?= (int)$task['id'] ?>">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <?php if (!empty($task['category_name'])): ?>
                            <span class="badge" style="background-color: <?= htmlspecialchars($task['category_color'] ?? '#6366f1', ENT_QUOTES) ?>; font-size: 0.65rem;">
                                <?= htmlspecialchars($task['category_name'], ENT_QUOTES) ?>
                            </span>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                        <span class="badge-priority priority-<?= $task['priority'] ?>">
                            <?= ucfirst($task['priority']) ?>
                        </span>
                    </div>

                    <a href="/tasks/<?= (int)$task['id'] ?>/edit" class="text-decoration-none text-reset fw-semibold d-block mb-2">
                        <?= htmlspecialchars($task['title'], ENT_QUOTES) ?>
                    </a>

                    <?php if (!empty($task['description'])): ?>
                        <div class="text-muted small text-truncate mb-2" style="font-size: 0.78rem;">
                            <?= htmlspecialchars($task['description'], ENT_QUOTES) ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top text-muted small" style="font-size: 0.72rem;">
                        <div>
                            <?php if (!empty($task['due_date'])): ?>
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= \App\Helpers\Sanitizer::formatDate($task['due_date'], 'M d') ?>
                            <?php else: ?>
                                <span>No date</span>
                            <?php endif; ?>
                        </div>
                        <a href="/tasks/<?= (int)$task['id'] ?>/edit" class="text-muted text-decoration-none">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Column: Completed -->
    <div class="kanban-column" id="column-completed">
        <div class="kanban-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check2-circle text-success"></i>
                <span class="fw-bold">Completed</span>
            </div>
            <span class="badge bg-success-subtle text-success border kanban-count">
                <?= count($boardTasks['completed'] ?? []) ?>
            </span>
        </div>
        <div class="kanban-tasks" data-status="completed">
            <?php foreach (($boardTasks['completed'] ?? []) as $task): ?>
                <div class="kanban-card" draggable="true" data-task-id="<?= (int)$task['id'] ?>">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <?php if (!empty($task['category_name'])): ?>
                            <span class="badge" style="background-color: <?= htmlspecialchars($task['category_color'] ?? '#6366f1', ENT_QUOTES) ?>; font-size: 0.65rem;">
                                <?= htmlspecialchars($task['category_name'], ENT_QUOTES) ?>
                            </span>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                        <span class="badge-priority priority-<?= $task['priority'] ?>">
                            <?= ucfirst($task['priority']) ?>
                        </span>
                    </div>

                    <a href="/tasks/<?= (int)$task['id'] ?>/edit" class="text-decoration-none text-reset fw-semibold d-block mb-2 text-decoration-line-through text-muted">
                        <?= htmlspecialchars($task['title'], ENT_QUOTES) ?>
                    </a>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top text-muted small" style="font-size: 0.72rem;">
                        <div>
                            <i class="bi bi-check2 text-success me-1"></i> Completed
                        </div>
                        <a href="/tasks/<?= (int)$task['id'] ?>/edit" class="text-muted text-decoration-none">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
