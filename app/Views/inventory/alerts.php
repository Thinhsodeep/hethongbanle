<?php require_once __DIR__ . '/_badge.php'; ?>
<h1 class="h3 mb-4">Cảnh báo tồn kho</h1>
<h2 class="h5 text-warning">Sắp hết</h2>
<table class="table table-bordered align-middle mb-4">
    <thead class="table-light">
        <tr><th>Chi nhánh</th><th>SKU</th><th>Sản phẩm</th><th>SL</th><th>Tối thiểu</th><th></th></tr>
    </thead>
    <tbody>
        <?php if (empty($lowStock)): ?>
        <tr><td colspan="6" class="text-muted">Không có sản phẩm sắp hết.</td></tr>
        <?php endif; ?>
        <?php foreach ($lowStock as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['branch_name']) ?></td>
            <td><?= htmlspecialchars($row['sku']) ?></td>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= (int) $row['quantity'] ?></td>
            <td><?= (int) $row['min_quantity'] ?></td>
            <td><span class="badge <?= stockBadgeClass($row['stock_status']) ?>"><?= htmlspecialchars($row['stock_status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<h2 class="h5 text-danger">Hết hàng</h2>
<table class="table table-bordered align-middle">
    <thead class="table-light">
        <tr><th>Chi nhánh</th><th>SKU</th><th>Sản phẩm</th><th>SL</th><th></th></tr>
    </thead>
    <tbody>
        <?php if (empty($outOfStock)): ?>
        <tr><td colspan="5" class="text-muted">Không có sản phẩm hết hàng.</td></tr>
        <?php endif; ?>
        <?php foreach ($outOfStock as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['branch_name']) ?></td>
            <td><?= htmlspecialchars($row['sku']) ?></td>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= (int) $row['quantity'] ?></td>
            <td><span class="badge <?= stockBadgeClass($row['stock_status']) ?>"><?= htmlspecialchars($row['stock_status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<p class="mt-3"><a href="<?= BASE_URL ?>/inventory/index">← Về tồn kho</a></p>
