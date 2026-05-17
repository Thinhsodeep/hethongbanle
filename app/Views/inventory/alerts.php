<?php require_once __DIR__ . '/_badge.php'; ?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Cảnh báo tồn kho</h1>
        <p class="subtext mb-0">Sản phẩm sắp hết và hết hàng</p>
    </div>
    <a href="<?= BASE_URL ?>/inventory/index" class="btn btn-ghost">← Tồn kho</a>
</div>
<div class="stripe-card mb-4">
    <h2 class="h3 mb-3" style="color:var(--color-warning)">Sắp hết</h2>
    <table class="table stripe-table mb-0">
        <thead>
            <tr><th>Chi nhánh</th><th>SKU</th><th>Sản phẩm</th><th>SL</th><th>Tối thiểu</th><th></th></tr>
        </thead>
        <tbody>
            <?php if (empty($lowStock)): ?>
            <tr><td colspan="6" class="subtext">Không có sản phẩm sắp hết.</td></tr>
            <?php endif; ?>
            <?php foreach ($lowStock as $row): ?>
            <tr>
                <td class="subtext"><?= htmlspecialchars($row['branch_name']) ?></td>
                <td><?= htmlspecialchars($row['sku']) ?></td>
                <td class="fw-500"><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= (int) $row['quantity'] ?></td>
                <td><?= (int) $row['min_quantity'] ?></td>
                <td><span class="<?= stockBadgeClass($row['stock_status']) ?>"><?= htmlspecialchars($row['stock_status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="stripe-card">
    <h2 class="h3 mb-3" style="color:var(--color-danger)">Hết hàng</h2>
    <table class="table stripe-table mb-0">
        <thead>
            <tr><th>Chi nhánh</th><th>SKU</th><th>Sản phẩm</th><th>SL</th><th></th></tr>
        </thead>
        <tbody>
            <?php if (empty($outOfStock)): ?>
            <tr><td colspan="5" class="subtext">Không có sản phẩm hết hàng.</td></tr>
            <?php endif; ?>
            <?php foreach ($outOfStock as $row): ?>
            <tr>
                <td class="subtext"><?= htmlspecialchars($row['branch_name']) ?></td>
                <td><?= htmlspecialchars($row['sku']) ?></td>
                <td class="fw-500"><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= (int) $row['quantity'] ?></td>
                <td><span class="<?= stockBadgeClass($row['stock_status']) ?>"><?= htmlspecialchars($row['stock_status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
