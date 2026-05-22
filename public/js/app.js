function initApp() {
    document.querySelectorAll('[data-confirm-delete]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(btn.getAttribute('data-confirm-delete') || 'Xác nhận xóa?')) {
                e.preventDefault();
            }
        });
    });

    // Sidebar toggle for mobile
    const btnToggleSidebar = document.getElementById('btnToggleSidebar');
    const sidebar = document.querySelector('.stripe-sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (btnToggleSidebar && sidebar && sidebarOverlay) {
        btnToggleSidebar.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });

        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
