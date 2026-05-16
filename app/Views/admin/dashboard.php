<?php $s = $summary ?? []; ?>
<h1 class="h3 mb-4">Dashboard</h1>
<div class="row g-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Chi nhánh active</div>
                <div class="fs-3 fw-bold"><?= (int) ($s['total_branches'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Sản phẩm active</div>
                <div class="fs-3 fw-bold"><?= (int) ($s['total_products'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Tổng tồn kho</div>
                <div class="fs-3 fw-bold"><?= number_format((int) ($s['total_stock'] ?? 0), 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-warning">
            <div class="card-body">
                <div class="text-muted small">Sắp hết / Hết hàng</div>
                <div class="fs-3 fw-bold text-warning"><?= (int) ($s['low_stock_count'] ?? 0) ?> / <?= (int) ($s['out_of_stock_count'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>
<p class="mt-4">
    <a href="<?= BASE_URL ?>/inventory/alerts" class="btn btn-outline-warning btn-sm">Xem cảnh báo tồn kho</a>
</p>
