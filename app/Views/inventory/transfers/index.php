<?php
// Helper badge
function transferBadge(string $status): string {
    return match($status) {
        'pending'   => 'badge bg-warning text-dark',
        'approved'  => 'badge bg-primary',
        'completed' => 'badge bg-success',
        'cancelled' => 'badge bg-danger',
        default     => 'badge bg-secondary',
    };
}
function transferLabel(string $status): string {
    return match($status) {
        'pending'   => 'Chờ duyệt',
        'approved'  => 'Đã duyệt',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
        default     => $status,
    };
}
?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Chuyển kho</h1>
        <p class="subtext mb-0">Quản lý phiếu chuyển hàng giữa các chi nhánh</p>
    </div>
    <div class="stripe-page-header-actions">
        <?php if (in_array($_SESSION['role'] ?? '', ['admin','manager','staff'], true)): ?>
        <a href="<?= BASE_URL ?>/transfer/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tạo phiếu chuyển
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="stripe-card p-0">
    <table class="table stripe-table mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>Từ chi nhánh</th>
                <th>Đến chi nhánh</th>
                <th>Người tạo</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transfers)): ?>
            <tr><td colspan="7" class="text-center subtext py-4">Chưa có phiếu chuyển kho nào.</td></tr>
            <?php else: ?>
            <?php foreach ($transfers as $t): ?>
            <tr>
                <td class="subtext">#<?= (int) $t['transfer_id'] ?></td>
                <td><span class="fw-500"><?= htmlspecialchars($t['from_branch_name']) ?></span></td>
                <td><?= htmlspecialchars($t['to_branch_name']) ?></td>
                <td class="subtext"><?= htmlspecialchars($t['created_by_name']) ?></td>
                <td><span class="<?= transferBadge($t['status']) ?>"><?= transferLabel($t['status']) ?></span></td>
                <td class="subtext"><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                <td class="text-end">
                    <a href="<?= BASE_URL ?>/transfer/detail/<?= (int) $t['transfer_id'] ?>" class="btn btn-ghost btn-sm">Chi tiết</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
