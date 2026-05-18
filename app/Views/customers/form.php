<?php
$isEdit = $customer !== null;
$action = $isEdit
    ? BASE_URL . '/customer/edit/' . (int) $customer['customer_id']
    : BASE_URL . '/customer/create';
?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0"><?= $isEdit ? 'Sửa khách hàng' : 'Thêm khách hàng' ?></h1>
        <p class="subtext mb-0"><?= $isEdit ? htmlspecialchars($customer['full_name']) : 'Điền thông tin khách hàng mới' ?></p>
    </div>
    <a href="<?= BASE_URL ?>/customer/index" class="btn btn-ghost">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="stripe-card">
            <form method="post" action="<?= $action ?>">
                <div class="mb-3">
                    <label class="label-text mb-1">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control stripe-input"
                           value="<?= htmlspecialchars($customer['full_name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="label-text mb-1">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control stripe-input"
                           value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="label-text mb-1">Email</label>
                    <input type="email" name="email" class="form-control stripe-input"
                           value="<?= htmlspecialchars($customer['email'] ?? '') ?>">
                </div>
                <div class="mb-4">
                    <label class="label-text mb-1">Địa chỉ</label>
                    <input type="text" name="address" class="form-control stripe-input"
                           value="<?= htmlspecialchars($customer['address'] ?? '') ?>">
                </div>
                <?php if ($isEdit): ?>
                <div class="mb-3 p-3 rounded" style="background:var(--surface-hover)">
                    <div class="d-flex justify-content-between">
                        <span class="label-text">Điểm tích lũy hiện tại</span>
                        <span class="badge bg-warning text-dark fs-6">
                            <i class="bi bi-star-fill me-1"></i><?= number_format((int) $customer['loyalty_points']) ?>
                        </span>
                    </div>
                </div>
                <?php endif; ?>
                <div class="d-flex gap-2 justify-content-end">
                    <a href="<?= BASE_URL ?>/customer/index" class="btn btn-ghost">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Lưu thay đổi' : 'Thêm khách hàng' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
