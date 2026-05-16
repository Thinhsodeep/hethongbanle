<nav class="sidebar bg-dark text-white p-3" style="min-width:220px;min-height:100vh">
    <div class="fw-bold mb-3">Retail Chain</div>
    <div class="small text-secondary mb-3"><?= htmlspecialchars($_SESSION['full_name'] ?? '') ?></div>
    <ul class="nav flex-column gap-1">
        <?php $role = $_SESSION['role'] ?? ''; ?>
        <?php if (in_array($role, ['admin', 'manager'], true)): ?>
            <li><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
            <li><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/branches">Chi nhánh</a></li>
        <?php endif; ?>
        <?php if (in_array($role, ['admin', 'manager'], true)): ?>
            <li><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/users">Nhân viên</a></li>
            <li><a class="nav-link text-white" href="<?= BASE_URL ?>/product/index">Sản phẩm</a></li>
            <li><a class="nav-link text-white" href="<?= BASE_URL ?>/product/categories">Danh mục</a></li>
        <?php endif; ?>
        <?php if (in_array($role, ['admin', 'manager', 'staff'], true)): ?>
            <li><a class="nav-link text-white" href="<?= BASE_URL ?>/inventory/index">Tồn kho</a></li>
            <li><a class="nav-link text-white" href="<?= BASE_URL ?>/inventory/alerts">Cảnh báo</a></li>
        <?php endif; ?>
        <li class="mt-3 border-top pt-2">
            <a class="nav-link text-danger" href="<?= BASE_URL ?>/auth/logout">Đăng xuất</a>
        </li>
    </ul>
</nav>
