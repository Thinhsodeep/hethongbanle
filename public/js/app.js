document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-confirm-delete]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(btn.getAttribute('data-confirm-delete') || 'Xác nhận xóa?')) {
                e.preventDefault();
            }
        });
    });
});
