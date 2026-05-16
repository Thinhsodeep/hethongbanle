<?php require_once __DIR__ . '/_badge.php'; ?>
<h1 class="h3 mb-4">Tồn kho</h1>
<?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
<form class="row g-2 mb-3" method="get" action="<?= BASE_URL ?>/inventory/index">
    <div class="col-auto">
        <select name="branch_id" class="form-select" onchange="this.form.submit()">
            <option value="0">Tất cả chi nhánh</option>
            <?php foreach ($branches as $b): ?>
            <option value="<?= (int) $b['branch_id'] ?>" <?= ($selectedBranch ?? null) === (int)$b['branch_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($b['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>
<?php endif; ?>
<div class="mb-3">
    <a href="<?= BASE_URL ?>/inventory/exportCsv<?= !empty($selectedBranch) ? '?branch_id=' . (int)$selectedBranch : '' ?>" class="btn btn-outline-success btn-sm">Xuất CSV</a>
    <a href="<?= BASE_URL ?>/inventory/alerts" class="btn btn-outline-warning btn-sm">Cảnh báo</a>
</div>
<table class="table table-hover table-bordered align-middle">
    <thead class="table-light">
        <tr>
            <th>Chi nhánh</th>
            <th>SKU</th>
            <th>Sản phẩm</th>
            <th>Danh mục</th>
            <th>SL</th>
            <th>Tối thiểu</th>
            <th>Trạng thái</th>
            <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'manager'], true)): ?>
            <th></th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($stocks as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['branch_name']) ?></td>
            <td><?= htmlspecialchars($row['sku']) ?></td>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= htmlspecialchars($row['category_name']) ?></td>
            <td><?= (int) $row['quantity'] ?></td>
            <td><?= (int) $row['min_quantity'] ?></td>
            <td><span class="badge <?= stockBadgeClass($row['stock_status']) ?>"><?= htmlspecialchars($row['stock_status']) ?></span></td>
            <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'manager'], true)): ?>
            <td>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#adjustModal"
                    data-branch="<?= (int) $row['branch_id'] ?>"
                    data-product="<?= (int) ($row['product_id'] ?? 0) ?>"
                    data-qty="<?= (int) $row['quantity'] ?>"
                    data-min="<?= (int) $row['min_quantity'] ?>"
                    data-name="<?= htmlspecialchars($row['product_name']) ?>">
                    Điều chỉnh
                </button>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if (in_array($_SESSION['role'] ?? '', ['admin', 'manager'], true)): ?>
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= BASE_URL ?>/inventory/adjust" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Điều chỉnh tồn kho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="adjustProductName" class="fw-bold"></p>
                <input type="hidden" name="branch_id" id="adjustBranchId">
                <input type="hidden" name="product_id" id="adjustProductId">
                <div class="mb-3">
                    <label class="form-label">Số lượng</label>
                    <input type="number" name="quantity" id="adjustQty" class="form-control" min="0" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ngưỡng cảnh báo</label>
                    <input type="number" name="min_quantity" id="adjustMin" class="form-control" min="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('adjustModal')?.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('adjustProductName').textContent = btn.getAttribute('data-name');
    document.getElementById('adjustBranchId').value = btn.getAttribute('data-branch');
    document.getElementById('adjustProductId').value = btn.getAttribute('data-product');
    document.getElementById('adjustQty').value = btn.getAttribute('data-qty');
    document.getElementById('adjustMin').value = btn.getAttribute('data-min');
});
</script>
<?php endif; ?>
