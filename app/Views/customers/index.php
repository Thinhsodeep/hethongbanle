<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Khách hàng</h1>
        <p class="subtext mb-0">Danh sách khách hàng và điểm tích lũy</p>
    </div>
    <div class="stripe-page-header-actions">
        <a href="<?= BASE_URL ?>/customer/create" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i>Thêm khách hàng
        </a>
    </div>
</div>

<form method="get" action="<?= BASE_URL ?>/customer/index" class="mb-3">
    <div class="input-group" style="max-width:360px">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" name="q" class="form-control stripe-input border-start-0"
               placeholder="Tìm theo tên, SĐT, email..." value="<?= htmlspecialchars($search) ?>">
        <?php if ($search): ?>
        <a href="<?= BASE_URL ?>/customer/index" class="btn btn-ghost">x</a>
        <?php endif; ?>
    </div>
</form>

<div class="stripe-card p-0">
    <table class="table stripe-table mb-0">
        <thead>
            <tr>
                <th>Tên khách hàng</th>
                <th>Điện thoại</th>
                <th>Email</th>
                <th class="text-end">Điểm tích lũy</th>
                <th>Ngày tạo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($customers)): ?>
            <tr><td colspan="6" class="text-center subtext py-4">
                <?= $search ? 'Không tìm thấy khách hàng nào.' : 'Chưa có khách hàng nào.' ?>
            </td></tr>
            <?php else: ?>
            <?php foreach ($customers as $c): ?>
            <tr>
                <td>
                    <div class="fw-500"><?= htmlspecialchars($c['full_name']) ?></div>
                    <?php if ($c['address']): ?>
                    <div class="subtext" style="font-size:.8rem"><?= htmlspecialchars($c['address']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="subtext"><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                <td class="subtext"><?= htmlspecialchars($c['email'] ?? '—') ?></td>
                <td class="text-end">
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-star-fill me-1"></i><?= number_format((int) $c['loyalty_points']) ?>
                    </span>
                </td>
                <td class="subtext"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                <td class="text-end">
                    <a href="<?= BASE_URL ?>/customer/history/<?= (int) $c['customer_id'] ?>" class="btn btn-ghost btn-sm">Lịch sử</a>
                    <a href="<?= BASE_URL ?>/customer/edit/<?= (int) $c['customer_id'] ?>" class="btn btn-ghost btn-sm">Sửa</a>
                    <?php if (in_array($_SESSION['role'] ?? '', ['admin','manager'], true)): ?>
                    <a href="<?= BASE_URL ?>/customer/delete/<?= (int) $c['customer_id'] ?>"
                       class="btn btn-ghost btn-sm text-danger"
                       onclick="return confirm('Xóa khách hàng này?')">Xóa</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
