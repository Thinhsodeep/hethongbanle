<?php
$totalSpent  = array_sum(array_column($history, 'final_amount'));
$totalOrders = count($history);
?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0"><?= htmlspecialchars($customer['full_name']) ?></h1>
        <p class="subtext mb-0">Lịch sử mua hàng & tích điểm</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/customer/edit/<?= (int) $customer['customer_id'] ?>" class="btn btn-ghost">Sửa thông tin</a>
        <a href="<?= BASE_URL ?>/customer/index" class="btn btn-ghost">
            <i class="bi bi-arrow-left me-1"></i>Danh sách
        </a>
    </div>
</div>

<!-- Thống kê tổng quan -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="stripe-card text-center">
            <div class="subtext mb-1">Tổng đơn hàng</div>
            <div class="fw-700" style="font-size:1.8rem"><?= $totalOrders ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stripe-card text-center">
            <div class="subtext mb-1">Tổng chi tiêu</div>
            <div class="fw-700 text-primary" style="font-size:1.8rem"><?= number_format($totalSpent, 0, ',', '.') ?>đ</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stripe-card text-center">
            <div class="subtext mb-1">Điểm tích lũy</div>
            <div class="fw-700" style="font-size:1.8rem">
                <i class="bi bi-star-fill text-warning me-1"></i><?= number_format((int) $customer['loyalty_points']) ?>
            </div>
        </div>
    </div>
</div>

<!-- Thông tin liên hệ -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stripe-card">
            <h3 class="h3 mb-3">Thông tin</h3>
            <dl class="row mb-0">
                <dt class="col-5 subtext">SĐT</dt>
                <dd class="col-7"><?= htmlspecialchars($customer['phone'] ?? '—') ?></dd>
                <dt class="col-5 subtext">Email</dt>
                <dd class="col-7"><?= htmlspecialchars($customer['email'] ?? '—') ?></dd>
                <dt class="col-5 subtext">Địa chỉ</dt>
                <dd class="col-7"><?= htmlspecialchars($customer['address'] ?? '—') ?></dd>
                <dt class="col-5 subtext">Thành viên từ</dt>
                <dd class="col-7"><?= date('d/m/Y', strtotime($customer['created_at'])) ?></dd>
            </dl>
        </div>
    </div>

    <div class="col-md-8">
        <div class="stripe-card p-0">
            <div class="p-3 border-bottom"><h3 class="h3 mb-0">Lịch sử đơn hàng</h3></div>
            <table class="table stripe-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Chi nhánh</th>
                        <th class="text-end">Tổng tiền</th>
                        <th class="text-end">Giảm giá</th>
                        <th class="text-end">Thực thu</th>
                        <th>Thanh toán</th>
                        <th>Ngày mua</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                    <tr><td colspan="7" class="text-center subtext py-3">Chưa có đơn hàng nào.</td></tr>
                    <?php else: ?>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td class="subtext">#<?= (int) $h['order_id'] ?></td>
                        <td><?= htmlspecialchars($h['branch_name']) ?></td>
                        <td class="text-end subtext"><?= number_format((float) $h['total_amount'], 0, ',', '.') ?>đ</td>
                        <td class="text-end text-danger">
                            <?= $h['discount'] > 0 ? '-' . number_format((float) $h['discount'], 0, ',', '.') . 'đ' : '—' ?>
                        </td>
                        <td class="text-end fw-500"><?= number_format((float) $h['final_amount'], 0, ',', '.') ?>đ</td>
                        <td>
                            <?php $pm = ['cash'=>'Tiền mặt','card'=>'Thẻ','transfer'=>'Chuyển khoản'];
                            echo $pm[$h['payment_method']] ?? $h['payment_method']; ?>
                        </td>
                        <td class="subtext"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
