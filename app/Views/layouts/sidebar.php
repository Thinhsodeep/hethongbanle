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
        <a href="<?= BASE_URL ?>/transfer/index" class="stripe-nav-item <?= $navActive('/transfer/') ?>">
            <i class="bi bi-arrow-left-right"></i> Chuyển kho
        </a>
        <?php endif; ?>
        <?php if (in_array($role, ['admin', 'manager'], true)): ?>
        <p class="label-text px-3 mb-1 mt-3">Nhập hàng</p>
        <a href="<?= BASE_URL ?>/purchase/index" class="stripe-nav-item <?= $navActive('/purchase/index') ?>">
            <i class="bi bi-truck"></i> Đơn nhập hàng
        </a>
        <a href="<?= BASE_URL ?>/purchase/suppliers" class="stripe-nav-item <?= $navActive('/purchase/supplier') ?>">
            <i class="bi bi-building-check"></i> Nhà cung cấp
        </a>
        <?php endif; ?>
        <?php if (in_array($role, ['admin', 'manager', 'cashier'], true)): ?>
        <p class="label-text px-3 mb-1 mt-3">Bán hàng</p>
        <a href="<?= BASE_URL ?>/pos/index" class="stripe-nav-item <?= $navActive('/pos/index') ?>">
            <i class="bi bi-cash-register"></i> Bán hàng (POS)
        </a>
        <a href="<?= BASE_URL ?>/pos/history" class="stripe-nav-item <?= $navActive('/pos/history') ?>">
            <i class="bi bi-receipt"></i> Lịch sử đơn
        </a>
        <a href="<?= BASE_URL ?>/customer/index" class="stripe-nav-item <?= $navActive('/customer/') ?>">
            <i class="bi bi-person-heart"></i> Khách hàng
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/auth/logout" class="stripe-nav-item text-danger mt-auto">
            <i class="bi bi-box-arrow-left"></i> Đăng xuất
        </a>
    </nav>
</aside>
