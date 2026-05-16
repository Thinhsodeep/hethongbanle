<h1 class="h3 mb-4">Chi nhánh</h1>
<a href="<?= BASE_URL ?>/admin/createBranch" class="btn btn-primary mb-3">Thêm chi nhánh</a>
<table class="table table-hover table-bordered align-middle">
    <thead class="table-light">
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
            <td><?= htmlspecialchars($b['name']) ?></td>
            <td><?= htmlspecialchars($b['address']) ?></td>
            <td><?= htmlspecialchars($b['phone'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['manager_name'] ?? '—') ?></td>
            <td><span class="badge bg-<?= $b['status'] === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($b['status']) ?></span></td>
            <td class="table-actions">
                <a href="<?= BASE_URL ?>/admin/editBranch/<?= (int) $b['branch_id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
