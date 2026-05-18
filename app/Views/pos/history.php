<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Lịch sử đơn bán</h1>
        <p class="subtext mb-0">Theo dõi đơn hàng theo ngày</p>
    </div>
    <a href="<?= BASE_URL ?>/pos/index" class="btn btn-primary">
        <i class="bi bi-cart-plus me-1"></i>Bán hàng
    </a>
</div>

<form method="get" action="<?= BASE_URL ?>/pos/history" class="mb-3 d-flex gap-2 align-items-center">
    <label class="label-text mb-0">Ngày:</label>
    <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="form-control stripe-input" style="max-width:180px">
    <button type="submit" class="btn btn-ghost btn-sm">Lọc</button>
</form>

<?php
$totalRevenue = array_sum(array_column(
    array_filter($orders, fn($o) => $o['status'] === 'completed'),
    'final_amount'
));
?>
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="stripe-card text-center">
            <div class="subtext mb-1">Số đơn hàng</div>
            <div class="fw-700" style="font-size:1.8rem"><?= count($orders) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stripe-card text-center">
            <div class="subtext mb-1">Doanh thu ngày</div>
            <div class="fw-700 text-primary" style="font-size:1.8rem"><?= number_format($totalRevenue, 0, ',', '.') ?>đ</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stripe-card text-center">
            <div class="subtext mb-1">Trung bình / đơn</div>
            <div class="fw-700" style="font-size:1.8rem">
                <?= count($orders) > 0 ? number_format($totalRevenue / count($orders), 0, ',', '.') . 'đ' : '—' ?>
            </div>
        </div>
    </div>
</div>

<div class="stripe-card p-0">
    <table class="table stripe-table mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>Thu ngân</th>
                <th>Khách hàng</th>
                <th class="text-end">Tổng tiền</th>
                <th class="text-end">Giảm giá</th>
                <th class="text-end">Thực thu</th>
                <th>Thanh toán</th>
                <th>Trạng thái</th>
                <th>Giờ</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
            <tr><td colspan="10" class="text-center subtext py-4">Không có đơn hàng nào trong ngày này.</td></tr>
            <?php else: ?>
            <?php foreach ($orders as $o):
                $pm = ['cash'=>'Tiền mặt','card'=>'Thẻ','transfer'=>'Chuyển khoản'];
            ?>
            <tr>
                <td class="subtext">#<?= (int) $o['order_id'] ?></td>
                <td class="fw-500"><?= htmlspecialchars($o['cashier_name']) ?></td>
                <td class="subtext"><?= htmlspecialchars($o['customer_name'] ?? 'Vãng lai') ?></td>
                <td class="text-end subtext"><?= number_format((float) $o['total_amount'], 0, ',', '.') ?>đ</td>
                <td class="text-end text-danger">
                    <?= $o['discount'] > 0 ? '-' . number_format((float) $o['discount'], 0, ',', '.') . 'đ' : '—' ?>
                </td>
                <td class="text-end fw-500"><?= number_format((float) $o['final_amount'], 0, ',', '.') ?>đ</td>
                <td class="subtext"><?= $pm[$o['payment_method']] ?? $o['payment_method'] ?></td>
                <td>
                    <span class="badge <?= $o['status'] === 'completed' ? 'bg-success' : 'bg-danger' ?>">
                        <?= $o['status'] === 'completed' ? 'Hoàn thành' : ucfirst($o['status']) ?>
                    </span>
                </td>
                <td class="subtext"><?= date('H:i', strtotime($o['created_at'])) ?></td>
                <td>
                    <a href="<?= BASE_URL ?>/pos/receipt/<?= (int) $o['order_id'] ?>" class="btn btn-ghost btn-sm" target="_blank">
                        <i class="bi bi-printer"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
