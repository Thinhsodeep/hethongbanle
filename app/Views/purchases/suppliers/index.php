<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Nhà cung cấp</h1>
        <p class="subtext mb-0">Quản lý danh sách nhà cung cấp</p>
    </div>
    <div class="stripe-page-header-actions">
        <a href="<?= BASE_URL ?>/purchase/index" class="btn btn-ghost">
            <i class="bi bi-arrow-left me-1"></i>Đơn nhập hàng
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#supplierModal" id="addSupplierBtn">
            <i class="bi bi-plus-lg me-1"></i>Thêm NCC
        </button>
    </div>
</div>

<div class="stripe-card p-0">
    <table class="table stripe-table mb-0">
        <thead>
            <tr>
                <th>Tên nhà cung cấp</th>
                <th>Điện thoại</th>
                <th>Email</th>
                <th>Địa chỉ</th>
                <th>Trạng thái</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($suppliers)): ?>
            <tr><td colspan="6" class="text-center subtext py-4">Chưa có nhà cung cấp nào.</td></tr>
            <?php else: ?>
            <?php foreach ($suppliers as $s): ?>
            <tr>
                <td class="fw-500"><?= htmlspecialchars($s['name']) ?></td>
                <td class="subtext"><?= htmlspecialchars($s['phone'] ?? '—') ?></td>
                <td class="subtext"><?= htmlspecialchars($s['email'] ?? '—') ?></td>
                <td class="subtext"><?= htmlspecialchars($s['address'] ?? '—') ?></td>
                <td>
                    <span class="badge <?= $s['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $s['status'] === 'active' ? 'Hoạt động' : 'Ngừng' ?>
                    </span>
                </td>
                <td class="text-end">
                    <button class="btn btn-ghost btn-sm" data-bs-toggle="modal" data-bs-target="#supplierModal"
                        data-id="<?= (int) $s['supplier_id'] ?>"
                        data-name="<?= htmlspecialchars($s['name']) ?>"
                        data-phone="<?= htmlspecialchars($s['phone'] ?? '') ?>"
                        data-email="<?= htmlspecialchars($s['email'] ?? '') ?>"
                        data-address="<?= htmlspecialchars($s['address'] ?? '') ?>"
                        data-status="<?= htmlspecialchars($s['status']) ?>">
                        Sửa
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal thêm/sửa NCC -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="<?= BASE_URL ?>/purchase/supplierSave" class="modal-content stripe-card p-0" style="border-radius:var(--radius-lg)">
            <div class="modal-body p-4">
                <h3 class="h3 mb-3" id="modalTitle">Thêm nhà cung cấp</h3>
                <input type="hidden" name="supplier_id" id="supplierId" value="0">
                <div class="mb-3">
                    <label class="label-text mb-1">Tên NCC <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="suppName" class="form-control stripe-input" required>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="label-text mb-1">Điện thoại</label>
                        <input type="text" name="phone" id="suppPhone" class="form-control stripe-input">
                    </div>
                    <div class="col-6">
                        <label class="label-text mb-1">Email</label>
                        <input type="email" name="email" id="suppEmail" class="form-control stripe-input">
                    </div>
                </div>
                <div class="mb-3 mt-2">
                    <label class="label-text mb-1">Địa chỉ</label>
                    <input type="text" name="address" id="suppAddress" class="form-control stripe-input">
                </div>
                <div class="mb-3" id="statusRow" style="display:none">
                    <label class="label-text mb-1">Trạng thái</label>
                    <select name="status" id="suppStatus" class="form-select stripe-input">
                        <option value="active">Hoạt động</option>
                        <option value="inactive">Ngừng</option>
                    </select>
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
document.getElementById('supplierModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const id  = btn?.dataset?.id || '0';
    document.getElementById('supplierId').value = id;
    document.getElementById('suppName').value    = btn?.dataset?.name    || '';
    document.getElementById('suppPhone').value   = btn?.dataset?.phone   || '';
    document.getElementById('suppEmail').value   = btn?.dataset?.email   || '';
    document.getElementById('suppAddress').value = btn?.dataset?.address || '';
    document.getElementById('modalTitle').textContent = id > 0 ? 'Sửa nhà cung cấp' : 'Thêm nhà cung cấp';
    document.getElementById('statusRow').style.display = id > 0 ? '' : 'none';
    if (id > 0) document.getElementById('suppStatus').value = btn?.dataset?.status || 'active';
});
</script>
