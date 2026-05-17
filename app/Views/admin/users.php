<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Nhân viên</h1>
        <p class="subtext mb-0">Danh sách tài khoản nhân viên</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/createUser" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Thêm nhân viên</a>
</div>
<div class="stripe-card p-0">
    <table class="table stripe-table mb-0">
        <thead>
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
                <td class="fw-500"><?= htmlspecialchars($u['full_name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td class="subtext"><?= htmlspecialchars($u['phone'] ?? '') ?></td>
                <td><span class="stripe-badge stripe-badge-accent"><?= htmlspecialchars($u['role_name']) ?></span></td>
                <td class="subtext"><?= htmlspecialchars($u['branch_name']) ?></td>
                <td>
                    <span class="stripe-badge <?= $u['status'] === 'active' ? 'stripe-badge-success' : 'stripe-badge-muted' ?>">
                        <?= htmlspecialchars($u['status']) ?>
                    </span>
                </td>
                <td class="text-end">
                    <a href="<?= BASE_URL ?>/admin/editUser/<?= (int) $u['user_id'] ?>" class="btn btn-ghost btn-sm">Sửa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
