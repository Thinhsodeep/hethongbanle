<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Chi nhánh</h1>
        <p class="subtext mb-0">Quản lý chi nhánh trong chuỗi</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/createBranch" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Thêm chi nhánh</a>
</div>
<div class="stripe-card p-0">
    <table class="table stripe-table mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>Tên</th>
                <th>Địa chỉ</th>
                <th>Điện thoại</th>
                <th>Quản lý</th>
                <th>Trạng thái</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($branches as $b): ?>
            <tr>
                <td><?= (int) $b['branch_id'] ?></td>
                <td class="fw-500"><?= htmlspecialchars($b['name']) ?></td>
                <td class="subtext"><?= htmlspecialchars($b['address']) ?></td>
                <td class="subtext"><?= htmlspecialchars($b['phone'] ?? '') ?></td>
                <td><?= htmlspecialchars($b['manager_name'] ?? '—') ?></td>
                <td>
                    <span class="stripe-badge <?= $b['status'] === 'active' ? 'stripe-badge-success' : 'stripe-badge-muted' ?>">
                        <?= htmlspecialchars($b['status']) ?>
                    </span>
                </td>
                <td class="table-actions text-end">
                    <a href="<?= BASE_URL ?>/admin/editBranch/<?= (int) $b['branch_id'] ?>" class="btn btn-ghost btn-sm">Sửa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
