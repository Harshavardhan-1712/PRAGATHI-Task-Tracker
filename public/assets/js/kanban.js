/**
 * PRAGATHI — Kanban Board Drag & Drop Controller
 * HTML5 native drag-and-drop with asynchronous status synchronization and optimistic updates.
 */

document.addEventListener('DOMContentLoaded', () => {
    initKanban();
});

function initKanban() {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-tasks');

    let draggedCard = null;
    let sourceColumn = null;

    cards.forEach(card => {
        card.addEventListener('dragstart', (e) => {
            draggedCard = card;
            sourceColumn = card.parentElement;
            card.classList.add('dragging');
            e.dataTransfer.setData('text/plain', card.dataset.taskId);
            e.dataTransfer.effectAllowed = 'move';
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            draggedCard = null;
        });
    });

    columns.forEach(column => {
        column.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            column.classList.add('dragover');
        });

        column.addEventListener('dragleave', () => {
            column.classList.remove('dragover');
        });

        column.addEventListener('drop', async (e) => {
            e.preventDefault();
            column.classList.remove('dragover');

            if (!draggedCard) return;

            const targetStatus = column.dataset.status;
            const sourceStatus = sourceColumn.dataset.status;
            const taskId = draggedCard.dataset.taskId;

            if (targetStatus === sourceStatus) {
                return; // Dropped in the same column
            }

            // Optimistically move card
            column.appendChild(draggedCard);
            updateColumnBadges();

            // Obtain CSRF token from page meta tag
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            try {
                const response = await fetch('/kanban/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        task_id: taskId,
                        status: targetStatus,
                        _csrf_token: csrfToken,
                    }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Failed to update task status.');
                }

                // Show success visual indication
                draggedCard.classList.add('border-success');
                setTimeout(() => draggedCard.classList.remove('border-success'), 1200);

            } catch (error) {
                // Revert card move on error
                sourceColumn.appendChild(draggedCard);
                updateColumnBadges();
                alert('Status update failed: ' + error.message);
            }
        });
    });

    function updateColumnBadges() {
        document.querySelectorAll('.kanban-column').forEach(col => {
            const status = col.querySelector('.kanban-tasks').dataset.status;
            const count = col.querySelectorAll('.kanban-card').length;
            const badge = col.querySelector('.kanban-count');
            if (badge) badge.textContent = count;
        });
    }
}
