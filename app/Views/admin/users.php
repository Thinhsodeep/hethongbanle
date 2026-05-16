<h1 class="h3 mb-4">Nhân viên</h1>
<a href="<?= BASE_URL ?>/admin/createUser" class="btn btn-primary mb-3">Thêm nhân viên</a>
<table class="table table-hover table-bordered align-middle">
    <thead class="table-light">
        <tr>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Điện thoại</th>
            <th>Vai trò</th>
            <th>Chi nhánh</th>
            <th>Trạng thái</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['full_name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['phone'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['role_name']) ?></td>
            <td><?= htmlspecialchars($u['branch_name']) ?></td>
            <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($u['status']) ?></span></td>
            <td>
                <a href="<?= BASE_URL ?>/admin/editUser/<?= (int) $u['user_id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
