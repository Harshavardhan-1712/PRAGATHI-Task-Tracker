<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Activity Timeline</h2>
        <p class="text-muted small mb-0">Immutable chronological audit trail of all task modifications and milestones.</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-4">
        <?php if (empty($activities)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-clock-history fs-1 text-secondary mb-2 d-block"></i>
                <h5 class="fw-semibold">No activity logs recorded yet</h5>
                <p class="small text-muted mb-0">Your actions will be automatically tracked here as you work.</p>
            </div>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($activities as $act): ?>
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <?php
                            $icon = match($act['action_type']) {
                                'created' => 'bi-plus-circle',
                                'completed' => 'bi-check2-circle text-success',
                                'status_changed' => 'bi-arrow-left-right text-info',
                                'deleted' => 'bi-trash text-danger',
                                'archived' => 'bi-archive text-secondary',
                                'restored' => 'bi-arrow-counterclockwise text-success',
                                default => 'bi-pencil text-primary'
                            };
                            ?>
                            <i class="bi <?= $icon ?>"></i>
                        </div>
                        <div class="card p-3 shadow-none border">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="badge bg-light text-secondary border small text-uppercase" style="font-size: 0.65rem;">
                                    <?= htmlspecialchars(str_replace('_', ' ', $act['action_type']), ENT_QUOTES) ?>
                                </span>
                                <span class="text-muted small" style="font-size: 0.75rem;">
                                    <?= \App\Helpers\Sanitizer::formatDate($act['created_at'], 'M d, Y h:i A') ?>
                                    (<?= \App\Helpers\Sanitizer::relativeTime($act['created_at']) ?>)
                                </span>
                            </div>
                            <div class="fw-semibold text-truncate">
                                <?= htmlspecialchars($act['description'], ENT_QUOTES) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-3">
                    <div class="small text-muted">
                        Page <?= $page ?> of <?= $totalPages ?> (<?= $totalLogs ?> total entries)
                    </div>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="/activity?page=<?= $page - 1 ?>">Previous</a>
                        </li>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="/activity?page=<?= $p ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="/activity?page=<?= $page + 1 ?>">Next</a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
