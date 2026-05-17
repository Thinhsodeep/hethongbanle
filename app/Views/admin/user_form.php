<?php
$isEdit = !empty($user);
$isManager = ($_SESSION['role'] ?? '') === 'manager';
?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0"><?= $isEdit ? 'Sửa nhân viên' : 'Thêm nhân viên' ?></h1>
        <p class="subtext mb-0">Thông tin tài khoản</p>
    </div>
</div>
<div class="stripe-card" style="max-width:640px">
    <form method="post" action="<?= BASE_URL ?>/admin/<?= $isEdit ? 'updateUser/' . (int) $user['user_id'] : 'storeUser' ?>">
        <div class="mb-3">
            <label class="label-text mb-1">Họ tên</label>
            <input type="text" name="full_name" class="form-control stripe-input" required value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="label-text mb-1">Email</label>
            <input type="email" name="email" class="form-control stripe-input" required value="<?= htmlspecialchars($user['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="label-text mb-1">Mật khẩu<?= $isEdit ? ' (để trống nếu không đổi)' : '' ?></label>
            <input type="password" name="password" class="form-control stripe-input" <?= $isEdit ? '' : 'required' ?>>
        </div>
        <div class="mb-3">
            <label class="label-text mb-1">Điện thoại</label>
            <input type="text" name="phone" class="form-control stripe-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        </div>
        <?php if (!$isManager): ?>
        <div class="mb-3">
            <label class="label-text mb-1">Chi nhánh</label>
            <select name="branch_id" class="form-select stripe-input" required>
                <?php foreach ($branches as $b): ?>
                <option value="<?= (int) $b['branch_id'] ?>" <?= (int)($user['branch_id'] ?? 0) === (int)$b['branch_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($b['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="mb-3">
            <label class="label-text mb-1">Vai trò</label>
            <select name="role_id" class="form-select stripe-input" required>
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
            <label class="label-text mb-1">Trạng thái</label>
            <select name="status" class="form-select stripe-input">
                <option value="active" <?= ($user['status'] ?? '') === 'active' ? 'selected' : '' ?>>active</option>
                <option value="inactive" <?= ($user['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>inactive</option>
            </select>
        </div>
        <?php else: ?>
        <input type="hidden" name="status" value="active">
        <?php endif; ?>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Lưu</button>
            <a href="<?= BASE_URL ?>/admin/users" class="btn btn-ghost">Hủy</a>
        </div>
    </form>
</div>
