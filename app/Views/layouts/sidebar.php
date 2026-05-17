<?php
$uri = $_SERVER['REQUEST_URI'] ?? '';
$navActive = static function (string $segment) use ($uri): string {
    return str_contains($uri, $segment) ? 'active' : '';
};
$role = $_SESSION['role'] ?? '';
?>
<aside class="stripe-sidebar">
    <div class="stripe-sidebar-brand">
        <div class="stripe-avatar stripe-avatar-dark" style="border-radius:var(--radius-md)">RC</div>
        <span>Retail Chain</span>
    </div>
    <?php if (!empty($_SESSION['full_name'])): ?>
    <div class="stripe-sidebar-user"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
    <?php endif; ?>
    <nav class="stripe-sidebar-nav">
        <?php if (in_array($role, ['admin', 'manager'], true)): ?>
        <p class="label-text px-3 mb-1 mt-2">Tổng quan</p>
        <a href="<?= BASE_URL ?>/admin/dashboard" class="stripe-nav-item <?= $navActive('/admin/dashboard') ?>">
            <i class="bi bi-grid"></i> Dashboard
        </a>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
        <p class="label-text px-3 mb-1 mt-3">Hệ thống</p>
        <a href="<?= BASE_URL ?>/admin/branches" class="stripe-nav-item <?= $navActive('/admin/branch') ?>">
            <i class="bi bi-building"></i> Chi nhánh
        </a>
        <?php endif; ?>
        <?php if (in_array($role, ['admin', 'manager'], true)): ?>
        <p class="label-text px-3 mb-1 mt-3">Quản lý</p>
        <a href="<?= BASE_URL ?>/admin/users" class="stripe-nav-item <?= $navActive('/admin/user') ?>">
            <i class="bi bi-people"></i> Nhân viên
        </a>
        <a href="<?= BASE_URL ?>/product/index" class="stripe-nav-item <?= $navActive('/product/') ?>">
            <i class="bi bi-box-seam"></i> Sản phẩm
        </a>
        <a href="<?= BASE_URL ?>/product/categories" class="stripe-nav-item <?= $navActive('/product/categories') ?>">
            <i class="bi bi-tags"></i> Danh mục
        </a>
        <?php endif; ?>
        <?php if (in_array($role, ['admin', 'manager', 'staff'], true)): ?>
        <p class="label-text px-3 mb-1 mt-3">Kho</p>
        <a href="<?= BASE_URL ?>/inventory/index" class="stripe-nav-item <?= $navActive('/inventory/index') ?>">
            <i class="bi bi-archive"></i> Tồn kho
        </a>
        <a href="<?= BASE_URL ?>/inventory/alerts" class="stripe-nav-item <?= $navActive('/inventory/alerts') ?>">
            <i class="bi bi-exclamation-triangle"></i> Cảnh báo
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/auth/logout" class="stripe-nav-item text-danger mt-auto">
            <i class="bi bi-box-arrow-left"></i> Đăng xuất
        </a>
    </nav>
</aside>
