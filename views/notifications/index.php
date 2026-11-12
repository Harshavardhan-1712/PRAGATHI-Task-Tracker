<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Notification Center</h2>
        <p class="text-muted small mb-0">System alerts, deadline warnings, and milestone notifications.</p>
    </div>
    <?php if (!empty($unreadNotificationCount)): ?>
        <form action="/notifications/read-all" method="POST" class="m-0">
            <?= \App\Helpers\View::csrfField() ?>
            <button type="submit" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-check2-all"></i>
                <span>Mark All as Read</span>
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($notifications)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash fs-1 text-secondary mb-2 d-block"></i>
                <h5 class="fw-semibold">No notifications</h5>
                <p class="small text-muted mb-0">You have no new alerts at this time.</p>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                    <?php
                    $isUnread = ((int)$n['is_read'] === 0);
                    $iconClass = match($n['type']) {
                        'warning' => 'bi-exclamation-triangle-fill text-warning',
                        'danger'  => 'bi-exclamation-octagon-fill text-danger',
                        'success' => 'bi-check-circle-fill text-success',
                        default   => 'bi-info-circle-fill text-info'
                    };
                    ?>
                    <div class="list-group-item p-3 d-flex align-items-start gap-3 <?= $isUnread ? 'bg-primary-subtle bg-opacity-25' : '' ?>">
                        <div class="fs-4 mt-1">
                            <i class="bi <?= $iconClass ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <h6 class="fw-bold mb-0 <?= $isUnread ? 'text-primary' : '' ?>">
                                    <?= htmlspecialchars($n['title'], ENT_QUOTES) ?>
                                </h6>
                                <span class="text-muted small" style="font-size: 0.72rem;">
                                    <?= \App\Helpers\Sanitizer::relativeTime($n['created_at']) ?>
                                </span>
                            </div>
                            <p class="mb-2 text-muted small">
                                <?= htmlspecialchars($n['message'], ENT_QUOTES) ?>
                            </p>
                            <div class="d-flex align-items-center gap-2">
                                <?php if (!empty($n['link'])): ?>
                                    <a href="<?= htmlspecialchars($n['link'], ENT_QUOTES) ?>" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                        View Task &rarr;
                                    </a>
                                <?php endif; ?>
                                <?php if ($isUnread): ?>
                                    <form action="/notifications/<?= (int)$n['id'] ?>/read" method="POST" class="d-inline m-0">
                                        <?= \App\Helpers\View::csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-link text-decoration-none text-muted py-0 px-1" style="font-size: 0.75rem;">
                                            Mark as read
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
