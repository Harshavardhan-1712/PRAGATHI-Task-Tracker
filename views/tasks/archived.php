<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Archived Tasks</h2>
        <p class="text-muted small mb-0">Review past accomplishments or restore tasks to your active queue.</p>
    </div>
    <a href="/tasks" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Active Tasks
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($tasks)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-archive fs-1 text-secondary mb-2 d-block"></i>
                <h5 class="fw-semibold">No archived tasks</h5>
                <p class="small text-muted mb-0">Your archive repository is empty.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th class="ps-3">Title</th>
                            <th>Priority</th>
                            <th>Archived Date</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold"><?= htmlspecialchars($task['title'], ENT_QUOTES) ?></div>
                                    <?php if (!empty($task['description'])): ?>
                                        <div class="text-muted small text-truncate" style="max-width: 400px;">
                                            <?= htmlspecialchars($task['description'], ENT_QUOTES) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-priority priority-<?= $task['priority'] ?>">
                                        <?= ucfirst($task['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small"><?= \App\Helpers\Sanitizer::formatDate($task['archived_at']) ?></div>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <form action="/tasks/<?= (int)$task['id'] ?>/restore" method="POST" class="d-inline">
                                            <?= \App\Helpers\View::csrfField() ?>
                                            <button type="submit" class="btn btn-outline-success" title="Restore Task">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                            </button>
                                        </form>
                                        <form action="/tasks/<?= (int)$task['id'] ?>/delete" method="POST" class="d-inline ms-1" onsubmit="return confirm('Permanently delete this archived task?');">
                                            <?= \App\Helpers\View::csrfField() ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Permanently Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
