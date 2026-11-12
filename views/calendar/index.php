<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Calendar Schedule</h2>
        <p class="text-muted small mb-0">Visualize due dates, deadlines, and project execution chronologically.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="/calendar?year=<?= date('Y') ?>&month=<?= date('n') ?>" class="btn btn-outline-secondary btn-sm">
            Today
        </a>
        <div class="btn-group btn-group-sm">
            <a href="/calendar?year=<?= $prevYear ?>&month=<?= $prevMonth ?>" class="btn btn-outline-secondary">
                <i class="bi bi-chevron-left"></i>
            </a>
            <span class="btn btn-outline-secondary fw-bold disabled" style="color: inherit; opacity: 1;">
                <?= $monthName ?> <?= $year ?>
            </span>
            <a href="/calendar?year=<?= $nextYear ?>&month=<?= $nextMonth ?>" class="btn btn-outline-secondary">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        <a href="/tasks/create" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm ms-2">
            <i class="bi bi-plus-lg"></i>
            <span>Add Task</span>
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="calendar-grid">
        <div class="calendar-day-header">Sun</div>
        <div class="calendar-day-header">Mon</div>
        <div class="calendar-day-header">Tue</div>
        <div class="calendar-day-header">Wed</div>
        <div class="calendar-day-header">Thu</div>
        <div class="calendar-day-header">Fri</div>
        <div class="calendar-day-header">Sat</div>

        <?php
        // Leading blank cells for days before the 1st
        for ($i = 0; $i < $firstDayOfWeek; $i++):
        ?>
            <div class="calendar-cell other-month"></div>
        <?php endfor; ?>

        <?php
        // Month days
        for ($day = 1; $day <= $daysInMonth; $day++):
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $isToday = ($dateStr === $todayDate);
            $dayTasks = $tasksByDate[$dateStr] ?? [];
        ?>
            <div class="calendar-cell <?= $isToday ? 'today' : '' ?>">
                <div class="calendar-cell-date"><?= $day ?></div>
                <div class="overflow-y-auto flex-grow-1" style="max-height: 90px;">
                    <?php foreach ($dayTasks as $t): ?>
                        <a href="/tasks/<?= (int)$t['id'] ?>/edit" 
                           class="calendar-chip" 
                           style="border-left-color: <?= htmlspecialchars($t['category_color'] ?? '#6366f1', ENT_QUOTES) ?>;"
                           title="<?= htmlspecialchars($t['title'], ENT_QUOTES) ?> (<?= ucfirst($t['status']) ?>)">
                            <span class="fw-semibold"><?= htmlspecialchars($t['title'], ENT_QUOTES) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>

        <?php
        // Trailing cells to complete 7-day grid row
        $totalCells = $firstDayOfWeek + $daysInMonth;
        $remainder = 7 - ($totalCells % 7);
        if ($remainder < 7):
            for ($j = 0; $j < $remainder; $j++):
        ?>
            <div class="calendar-cell other-month"></div>
        <?php 
            endfor;
        endif; 
        ?>
    </div>
</div>
