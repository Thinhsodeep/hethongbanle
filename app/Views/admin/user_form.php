<?php
$isEdit = !empty($user);
$isManager = ($_SESSION['role'] ?? '') === 'manager';
?>
<h1 class="h3 mb-4"><?= $isEdit ? 'Sửa nhân viên' : 'Thêm nhân viên' ?></h1>
<form method="post" action="<?= BASE_URL ?>/admin/<?= $isEdit ? 'updateUser/' . (int) $user['user_id'] : 'storeUser' ?>">
    <div class="mb-3">
        <label class="form-label">Họ tên</label>
        <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Mật khẩu<?= $isEdit ? ' (để trống nếu không đổi)' : '' ?></label>
        <input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>>
    </div>
    <div class="mb-3">
        <label class="form-label">Điện thoại</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
    </div>
    <?php if (!$isManager): ?>
    <div class="mb-3">
        <label class="form-label">Chi nhánh</label>
        <select name="branch_id" class="form-select" required>
            <?php foreach ($branches as $b): ?>
            <option value="<?= (int) $b['branch_id'] ?>" <?= (int)($user['branch_id'] ?? 0) === (int)$b['branch_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($b['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div class="mb-3">
        <label class="form-label">Vai trò</label>
        <select name="role_id" class="form-select" required>
            <?php foreach ($roles as $r): ?>
            <?php if ($isManager && $r['role_name'] === 'admin') continue; ?>
            <option value="<?= (int) $r['role_id'] ?>" <?= (int)($user['role_id'] ?? 0) === (int)$r['role_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($r['role_name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($isEdit): ?>
    <div class="mb-3">
        <label class="form-label">Trạng thái</label>
        <select name="status" class="form-select">
            <option value="active" <?= ($user['status'] ?? '') === 'active' ? 'selected' : '' ?>>active</option>
            <option value="inactive" <?= ($user['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>inactive</option>
        </select>
    </div>
    <?php else: ?>
    <input type="hidden" name="status" value="active">
    <?php endif; ?>
    <button type="submit" class="btn btn-primary">Lưu</button>
    <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary">Hủy</a>
</form>
