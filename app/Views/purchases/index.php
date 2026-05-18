<?php
function poBadge(string $status): string {
    return match($status) {
        'pending'  => 'badge bg-warning text-dark',
        'received' => 'badge bg-success',
        'cancelled'=> 'badge bg-danger',
        default    => 'badge bg-secondary',
    };
}
function poLabel(string $status): string {
    return match($status) {
        'pending'   => 'Chờ nhận',
        'received'  => 'Đã nhận',
        'cancelled' => 'Đã hủy',
        default     => $status,
    };
}
?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Đơn nhập hàng</h1>
        <p class="subtext mb-0">Quản lý đơn nhập hàng từ nhà cung cấp</p>
    </div>
    <div class="stripe-page-header-actions">
        <a href="<?= BASE_URL ?>/purchase/suppliers" class="btn btn-ghost">
            <i class="bi bi-building me-1"></i>Nhà cung cấp
        </a>
        <a href="<?= BASE_URL ?>/purchase/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tạo đơn nhập
        </a>
    </div>
</div>

<div class="stripe-card p-0">
    <table class="table stripe-table mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>Chi nhánh</th>
                <th>Nhà cung cấp</th>
                <th>Người tạo</th>
                <th class="text-end">Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
            <tr><td colspan="8" class="text-center subtext py-4">Chưa có đơn nhập hàng nào.</td></tr>
            <?php else: ?>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td class="subtext">#<?= (int) $o['po_id'] ?></td>
                <td class="fw-500"><?= htmlspecialchars($o['branch_name']) ?></td>
                <td><?= htmlspecialchars($o['supplier_name']) ?></td>
                <td class="subtext"><?= htmlspecialchars($o['created_by_name']) ?></td>
                <td class="text-end fw-500"><?= number_format((float) $o['total_amount'], 0, ',', '.') ?>đ</td>
                <td><span class="<?= poBadge($o['status']) ?>"><?= poLabel($o['status']) ?></span></td>
                <td class="subtext"><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
                <td class="text-end">
                    <a href="<?= BASE_URL ?>/purchase/detail/<?= (int) $o['po_id'] ?>" class="btn btn-ghost btn-sm">Chi tiết</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
