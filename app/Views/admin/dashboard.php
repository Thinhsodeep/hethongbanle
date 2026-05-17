<?php $s = $summary ?? []; ?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Dashboard</h1>
        <p class="subtext mb-0">Tổng quan tồn kho và hệ thống</p>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stripe-card">
            <p class="label-text mb-2">Chi nhánh active</p>
            <div class="h1 mb-0" style="color:var(--color-accent)"><?= (int) ($s['total_branches'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stripe-card">
            <p class="label-text mb-2">Sản phẩm active</p>
            <div class="h1 mb-0" style="color:var(--color-accent)"><?= (int) ($s['total_products'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stripe-card">
            <p class="label-text mb-2">Tổng tồn kho</p>
            <div class="h1 mb-0" style="color:var(--color-accent)"><?= number_format((int) ($s['total_stock'] ?? 0), 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stripe-card">
            <p class="label-text mb-2" style="color:var(--color-warning)">Sắp hết / Hết hàng</p>
            <div class="h1 mb-0" style="color:var(--color-warning)"><?= (int) ($s['low_stock_count'] ?? 0) ?> / <?= (int) ($s['out_of_stock_count'] ?? 0) ?></div>
        </div>
    </div>
</div>
<a href="<?= BASE_URL ?>/inventory/alerts" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-exclamation-triangle me-1"></i> Xem cảnh báo tồn kho
</a>
