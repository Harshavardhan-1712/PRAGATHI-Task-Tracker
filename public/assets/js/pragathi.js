/**
 * PRAGATHI — Core Application Scripts
 * Theme switching, toast handling, select-all checkboxes, mobile sidebar
 */

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initToasts();
    initBulkSelection();
    initSidebarToggle();
});

// ---------------- Theme Management ----------------

function initTheme() {
    const savedTheme = localStorage.getItem('pragathi_theme') || document.documentElement.getAttribute('data-bs-theme') || 'light';
    setTheme(savedTheme);

    const toggleBtns = document.querySelectorAll('.theme-toggle-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            setTheme(next);
            localStorage.setItem('pragathi_theme', next);
        });
    });
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    const icons = document.querySelectorAll('.theme-toggle-icon');
    icons.forEach(icon => {
        if (theme === 'dark') {
            icon.classList.remove('bi-moon-stars-fill');
            icon.classList.add('bi-sun-fill');
        } else {
            icon.classList.remove('bi-sun-fill');
            icon.classList.add('bi-moon-stars-fill');
        }
    });
}

// ---------------- Bootstrap Toasts ----------------

function initToasts() {
    const toastElList = document.querySelectorAll('.toast');
    toastElList.forEach(toastEl => {
        const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
        toast.show();
    });
}

// ---------------- Bulk Checkbox Selection ----------------

function initBulkSelection() {
    const selectAllCheckbox = document.getElementById('selectAllTasks');
    const taskCheckboxes = document.querySelectorAll('.task-checkbox');
    const bulkBar = document.getElementById('bulkActionBar');
    const selectedCountSpan = document.getElementById('selectedCount');

    if (!selectAllCheckbox || !taskCheckboxes.length) return;

    selectAllCheckbox.addEventListener('change', () => {
        taskCheckboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
        });
        updateBulkBar();
    });

    taskCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const allChecked = Array.from(taskCheckboxes).every(c => c.checked);
            selectAllCheckbox.checked = allChecked;
            updateBulkBar();
        });
    });

    function updateBulkBar() {
        const checkedCount = Array.from(taskCheckboxes).filter(c => c.checked).length;
        if (selectedCountSpan) {
            selectedCountSpan.textContent = checkedCount;
        }
        if (bulkBar) {
            if (checkedCount > 0) {
                bulkBar.classList.remove('d-none');
            } else {
                bulkBar.classList.add('d-none');
            }
        }
    }
}

// ---------------- Mobile Sidebar ----------------

function initSidebarToggle() {
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebar = document.getElementById('appSidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992 && sidebar.classList.contains('show')) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }
}
