<?php
$role   = $_SESSION['role'] ?? '';
$status = $order['status'];
$badge  = match($status) {
    'pending'   => 'badge bg-warning text-dark',
    'received'  => 'badge bg-success',
    'cancelled' => 'badge bg-danger',
    default     => 'badge bg-secondary',
};
$label = match($status) {
    'pending'  => 'Chờ nhận',
    'received' => 'Đã nhận',
    'cancelled'=> 'Đã hủy',
    default    => $status,
};
?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Đơn nhập hàng #<?= (int) $order['po_id'] ?></h1>
        <p class="subtext mb-0">
            <?= htmlspecialchars($order['branch_name']) ?> —
            NCC: <?= htmlspecialchars($order['supplier_name']) ?>
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="<?= $badge ?> fs-6"><?= $label ?></span>
        <a href="<?= BASE_URL ?>/purchase/index" class="btn btn-ghost">
            <i class="bi bi-arrow-left me-1"></i>Danh sách
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="stripe-card">
            <h3 class="h3 mb-3">Thông tin đơn</h3>
            <dl class="row mb-0">
                <dt class="col-5 subtext">Chi nhánh</dt>
                <dd class="col-7 fw-500"><?= htmlspecialchars($order['branch_name']) ?></dd>
                <dt class="col-5 subtext">Nhà cung cấp</dt>
                <dd class="col-7 fw-500"><?= htmlspecialchars($order['supplier_name']) ?></dd>
                <dt class="col-5 subtext">Người tạo</dt>
                <dd class="col-7"><?= htmlspecialchars($order['created_by_name']) ?></dd>
                <dt class="col-5 subtext">Ngày tạo</dt>
                <dd class="col-7"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></dd>
                <dt class="col-5 subtext">Tổng tiền</dt>
                <dd class="col-7 fw-700 text-primary"><?= number_format((float) $order['total_amount'], 0, ',', '.') ?>đ</dd>
                <?php if ($order['note']): ?>
                <dt class="col-5 subtext">Ghi chú</dt>
                <dd class="col-7"><?= nl2br(htmlspecialchars($order['note'])) ?></dd>
                <?php endif; ?>
            </dl>
        </div>

        <?php if ($status === 'pending' && in_array($role, ['admin','manager'], true)): ?>
        <div class="stripe-card mt-4">
            <h3 class="h3 mb-3">Thao tác</h3>
            <a href="<?= BASE_URL ?>/purchase/receive/<?= (int) $order['po_id'] ?>"
               class="btn btn-success w-100 mb-2"
               onclick="return confirm('Xác nhận nhận hàng — tồn kho sẽ được cộng ngay?')">
                <i class="bi bi-box-arrow-in-down me-1"></i>Nhận hàng
            </a>
            <a href="<?= BASE_URL ?>/purchase/cancel/<?= (int) $order['po_id'] ?>"
               class="btn btn-ghost w-100 text-danger"
               onclick="return confirm('Hủy đơn nhập hàng này?')">
                <i class="bi bi-x-circle me-1"></i>Hủy đơn
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-8">
        <div class="stripe-card p-0">
            <div class="p-3 border-bottom"><h3 class="h3 mb-0">Danh sách sản phẩm nhập</h3></div>
            <table class="table stripe-table mb-0">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>SKU</th>
                        <th class="text-end">Số lượng</th>
                        <th class="text-end">Đơn giá</th>
                        <th class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; foreach ($items as $item):
                        $sub = $item['quantity'] * $item['unit_price'];
                        $total += $sub;
                    ?>
                    <tr>
                        <td class="fw-500"><?= htmlspecialchars($item['product_name']) ?></td>
                        <td class="subtext"><?= htmlspecialchars($item['sku']) ?></td>
                        <td class="text-end"><?= number_format((int) $item['quantity']) ?></td>
                        <td class="text-end subtext"><?= number_format((float) $item['unit_price'], 0, ',', '.') ?>đ</td>
                        <td class="text-end fw-500"><?= number_format($sub, 0, ',', '.') ?>đ</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-light">
                        <td colspan="4" class="text-end fw-700">Tổng cộng</td>
                        <td class="text-end fw-700 text-primary"><?= number_format($total, 0, ',', '.') ?>đ</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
