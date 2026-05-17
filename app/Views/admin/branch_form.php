<?php $isEdit = !empty($branch); ?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0"><?= $isEdit ? 'Sửa chi nhánh' : 'Thêm chi nhánh' ?></h1>
        <p class="subtext mb-0">Thông tin chi nhánh</p>
    </div>
</div>
<div class="stripe-card" style="max-width:640px">
    <form method="post" action="<?= BASE_URL ?>/admin/<?= $isEdit ? 'updateBranch/' . (int) $branch['branch_id'] : 'storeBranch' ?>">
        <div class="mb-3">
            <label class="label-text mb-1">Tên chi nhánh</label>
            <input type="text" name="name" class="form-control stripe-input" required value="<?= htmlspecialchars($branch['name'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="label-text mb-1">Địa chỉ</label>
            <input type="text" name="address" class="form-control stripe-input" required value="<?= htmlspecialchars($branch['address'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="label-text mb-1">Điện thoại</label>
            <input type="text" name="phone" class="form-control stripe-input" value="<?= htmlspecialchars($branch['phone'] ?? '') ?>">
        </div>
        <?php if ($isEdit): ?>
        <div class="mb-3">
            <label class="label-text mb-1">Quản lý</label>
            <select name="manager_id" class="form-select stripe-input">
                <option value="">— Chưa gán —</option>
                <?php foreach ($managers as $m): ?>
                <option value="<?= (int) $m['user_id'] ?>" <?= (int)($branch['manager_id'] ?? 0) === (int)$m['user_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['full_name'] . ' (' . $m['branch_name'] . ')') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="label-text mb-1">Trạng thái</label>
            <select name="status" class="form-select stripe-input">
                <option value="active" <?= ($branch['status'] ?? '') === 'active' ? 'selected' : '' ?>>active</option>
                <option value="inactive" <?= ($branch['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>inactive</option>
            </select>
        </div>
        <?php endif; ?>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Lưu</button>
            <a href="<?= BASE_URL ?>/admin/branches" class="btn btn-ghost">Hủy</a>
        </div>
    </form>
</div>
