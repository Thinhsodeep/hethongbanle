<?php require_once __DIR__ . '/_badge.php'; ?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Tồn kho</h1>
        <p class="subtext mb-0">Theo dõi tồn kho theo chi nhánh</p>
    </div>
    <div class="stripe-page-header-actions">
        <a href="<?= BASE_URL ?>/inventory/alerts" class="btn btn-ghost">Cảnh báo</a>
        <a href="<?= BASE_URL ?>/inventory/exportCsv<?= !empty($selectedBranch) ? '?branch_id=' . (int)$selectedBranch : '' ?>" class="btn btn-outline-primary btn-sm">Xuất CSV</a>
    </div>
</div>
<?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
<form class="mb-3" method="get" action="<?= BASE_URL ?>/inventory/index">
    <select name="branch_id" class="form-select stripe-input" style="max-width:280px" onchange="this.form.submit()">
        <option value="0">Tất cả chi nhánh</option>
        <?php foreach ($branches as $b): ?>
        <option value="<?= (int) $b['branch_id'] ?>" <?= ($selectedBranch ?? null) === (int)$b['branch_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($b['name']) ?>
        </option>
        <?php endforeach; ?>
    </select>
</form>
<?php endif; ?>
<div class="stripe-card p-0">
    <table class="table stripe-table mb-0">
        <thead>
            <tr>
                <th>Chi nhánh</th>
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
                <td class="subtext"><?= htmlspecialchars($row['branch_name']) ?></td>
                <td>
                    <div class="fw-500"><?= htmlspecialchars($row['product_name']) ?></div>
                    <div class="subtext" style="font-size:.8rem"><?= htmlspecialchars($row['sku']) ?></div>
                </td>
                <td class="subtext"><?= htmlspecialchars($row['category_name']) ?></td>
                <td><?= (int) $row['quantity'] ?></td>
                <td><?= (int) $row['min_quantity'] ?></td>
                <td><span class="<?= stockBadgeClass($row['stock_status']) ?>"><?= htmlspecialchars($row['stock_status']) ?></span></td>
                <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'manager'], true)): ?>
                <td class="text-end">
                    <button type="button" class="btn btn-ghost btn-sm" data-bs-toggle="modal" data-bs-target="#adjustModal"
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
</div>

<?php if (in_array($_SESSION['role'] ?? '', ['admin', 'manager'], true)): ?>
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="<?= BASE_URL ?>/inventory/adjust" class="modal-content stripe-card p-0" style="border-radius:var(--radius-lg)">
            <div class="modal-body p-4">
                <h3 class="h3 mb-1">Điều chỉnh tồn kho</h3>
                <p id="adjustProductName" class="subtext fw-500"></p>
                <input type="hidden" name="branch_id" id="adjustBranchId">
                <input type="hidden" name="product_id" id="adjustProductId">
                <div class="mb-3">
                    <label class="label-text mb-1">Số lượng</label>
                    <input type="number" name="quantity" id="adjustQty" class="form-control stripe-input" min="0" required>
                </div>
                <div class="mb-3">
                    <label class="label-text mb-1">Ngưỡng cảnh báo</label>
                    <input type="number" name="min_quantity" id="adjustMin" class="form-control stripe-input" min="0" required>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
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
