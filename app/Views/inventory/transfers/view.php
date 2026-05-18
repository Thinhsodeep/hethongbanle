<?php
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
$role   = $_SESSION['role'] ?? '';
$status = $transfer['status'];
?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Phiếu chuyển kho #<?= (int) $transfer['transfer_id'] ?></h1>
        <p class="subtext mb-0">
            <?= htmlspecialchars($transfer['from_branch_name']) ?>
            <i class="bi bi-arrow-right mx-1"></i>
            <?= htmlspecialchars($transfer['to_branch_name']) ?>
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="<?= transferBadge($status) ?> fs-6"><?= transferLabel($status) ?></span>
        <a href="<?= BASE_URL ?>/transfer/index" class="btn btn-ghost">
            <i class="bi bi-arrow-left me-1"></i>Danh sách
        </a>
    </div>
</div>

<!-- Timeline trạng thái -->
<div class="stripe-card mb-4">
    <div class="d-flex gap-0 align-items-center">
        <?php
        $steps = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'completed' => 'Hoàn thành'];
        $order = ['pending', 'approved', 'completed'];
        $currentIdx = array_search($status, $order);
        if ($status === 'cancelled') $currentIdx = -1;
        foreach ($order as $i => $s):
            $done    = ($currentIdx !== false && $i <= $currentIdx);
            $active  = ($currentIdx !== false && $i === $currentIdx);
        ?>
        <div class="d-flex align-items-center <?= $i > 0 ? 'flex-grow-1' : '' ?>">
            <?php if ($i > 0): ?>
            <div style="height:2px;flex:1;background:<?= $done ? 'var(--brand)' : '#dee2e6' ?>"></div>
            <?php endif; ?>
            <div class="d-flex flex-column align-items-center" style="min-width:80px">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:32px;height:32px;background:<?= $done ? 'var(--brand)' : '#dee2e6' ?>;color:<?= $done ? '#fff' : '#adb5bd' ?>">
                    <?= $done ? '<i class="bi bi-check-lg"></i>' : ($i + 1) ?>
                </div>
                <small class="subtext mt-1 text-center" style="font-size:.75rem"><?= $steps[$s] ?></small>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if ($status === 'cancelled'): ?>
        <div class="ms-3"><span class="badge bg-danger">Đã hủy</span></div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="stripe-card">
            <h3 class="h3 mb-3">Thông tin phiếu</h3>
            <dl class="row mb-0">
                <dt class="col-5 subtext">Từ chi nhánh</dt>
                <dd class="col-7 fw-500"><?= htmlspecialchars($transfer['from_branch_name']) ?></dd>
                <dt class="col-5 subtext">Đến chi nhánh</dt>
                <dd class="col-7 fw-500"><?= htmlspecialchars($transfer['to_branch_name']) ?></dd>
                <dt class="col-5 subtext">Người tạo</dt>
                <dd class="col-7"><?= htmlspecialchars($transfer['created_by_name']) ?></dd>
                <dt class="col-5 subtext">Ngày tạo</dt>
                <dd class="col-7"><?= date('d/m/Y H:i', strtotime($transfer['created_at'])) ?></dd>
                <?php if ($transfer['note']): ?>
                <dt class="col-5 subtext">Ghi chú</dt>
                <dd class="col-7"><?= nl2br(htmlspecialchars($transfer['note'])) ?></dd>
                <?php endif; ?>
            </dl>
        </div>

        <!-- Actions -->
        <?php if ($status !== 'cancelled' && $status !== 'completed'): ?>
        <div class="stripe-card mt-4">
            <h3 class="h3 mb-3">Thao tác</h3>
            <?php if ($status === 'pending' && in_array($role, ['admin','manager'], true)): ?>
            <a href="<?= BASE_URL ?>/transfer/approve/<?= (int) $transfer['transfer_id'] ?>"
               class="btn btn-primary w-100 mb-2"
               onclick="return confirm('Duyệt phiếu chuyển kho này?')">
                <i class="bi bi-check-circle me-1"></i>Duyệt phiếu
            </a>
            <?php endif; ?>
            <?php if ($status === 'approved' && in_array($role, ['admin','manager','staff'], true)): ?>
            <a href="<?= BASE_URL ?>/transfer/complete/<?= (int) $transfer['transfer_id'] ?>"
               class="btn btn-success w-100 mb-2"
               onclick="return confirm('Xác nhận hoàn thành — tồn kho sẽ được cập nhật ngay?')">
                <i class="bi bi-box-arrow-in-right me-1"></i>Hoàn thành chuyển kho
            </a>
            <?php endif; ?>
            <?php if (in_array($role, ['admin','manager'], true)): ?>
            <a href="<?= BASE_URL ?>/transfer/cancel/<?= (int) $transfer['transfer_id'] ?>"
               class="btn btn-ghost w-100 text-danger"
               onclick="return confirm('Hủy phiếu chuyển kho này?')">
                <i class="bi bi-x-circle me-1"></i>Hủy phiếu
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-8">
        <div class="stripe-card p-0">
            <div class="p-3 border-bottom"><h3 class="h3 mb-0">Danh sách sản phẩm</h3></div>
            <table class="table stripe-table mb-0">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>SKU</th>
                        <th>ĐVT</th>
                        <th class="text-end">Số lượng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="fw-500"><?= htmlspecialchars($item['product_name']) ?></td>
                        <td class="subtext"><?= htmlspecialchars($item['sku']) ?></td>
                        <td class="subtext"><?= htmlspecialchars($item['unit']) ?></td>
                        <td class="text-end fw-500"><?= number_format((int) $item['quantity']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($items)): ?>
                    <tr><td colspan="4" class="text-center subtext py-3">Không có sản phẩm.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
