<?php $isEdit = !empty($branch); ?>
<h1 class="h3 mb-4"><?= $isEdit ? 'Sửa chi nhánh' : 'Thêm chi nhánh' ?></h1>
<form method="post" action="<?= BASE_URL ?>/admin/<?= $isEdit ? 'updateBranch/' . (int) $branch['branch_id'] : 'storeBranch' ?>">
    <div class="mb-3">
        <label class="form-label">Tên chi nhánh</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($branch['name'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Địa chỉ</label>
        <input type="text" name="address" class="form-control" required value="<?= htmlspecialchars($branch['address'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Điện thoại</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($branch['phone'] ?? '') ?>">
    </div>
    <?php if ($isEdit): ?>
    <div class="mb-3">
        <label class="form-label">Quản lý</label>
        <select name="manager_id" class="form-select">
            <option value="">— Chưa gán —</option>
            <?php foreach ($managers as $m): ?>
            <option value="<?= (int) $m['user_id'] ?>" <?= (int)($branch['manager_id'] ?? 0) === (int)$m['user_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['full_name'] . ' (' . $m['branch_name'] . ')') ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Trạng thái</label>
        <select name="status" class="form-select">
            <option value="active" <?= ($branch['status'] ?? '') === 'active' ? 'selected' : '' ?>>active</option>
            <option value="inactive" <?= ($branch['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>inactive</option>
        </select>
    </div>
    <?php endif; ?>
    <button type="submit" class="btn btn-primary">Lưu</button>
    <a href="<?= BASE_URL ?>/admin/branches" class="btn btn-secondary">Hủy</a>
</form>
