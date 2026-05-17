<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Sản phẩm</h1>
        <p class="subtext mb-0">Quản lý toàn bộ sản phẩm trong hệ thống</p>
    </div>
    <div class="stripe-page-header-actions">
        <a href="<?= BASE_URL ?>/product/categories" class="btn btn-ghost">Danh mục</a>
        <a href="<?= BASE_URL ?>/product/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Thêm sản phẩm</a>
    </div>
</div>
<div class="stripe-card p-0">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom flex-wrap gap-2">
        <p class="subtext mb-0">Cập nhật danh sách sản phẩm</p>
        <form class="d-flex gap-2 flex-wrap" method="get" action="<?= BASE_URL ?>/product/index">
            <input type="text" name="kw" class="stripe-input" placeholder="Tìm SKU/tên..." value="<?= htmlspecialchars($kw ?? '') ?>" style="width:200px">
            <select name="cat" class="form-select stripe-input" style="width:180px">
                <option value="0">Tất cả danh mục</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= (int) $c['category_id'] ?>" <?= ($cat ?? 0) === (int)$c['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-ghost">Lọc</button>
        </form>
    </div>
    <table class="table stripe-table mb-0">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Danh mục</th>
                <th>Giá bán</th>
                <th>Trạng thái</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stripe-avatar"><?= mb_strtoupper(mb_substr($p['name'], 0, 2)) ?></div>
                        <div>
                            <div class="fw-500"><?= htmlspecialchars($p['name']) ?></div>
                            <div class="subtext" style="font-size:.8rem"><?= htmlspecialchars($p['sku']) ?></div>
                        </div>
                    </div>
                </td>
                <td class="subtext"><?= htmlspecialchars($p['category_name']) ?></td>
                <td><?= number_format((float) $p['sell_price'], 0, ',', '.') ?> ₫</td>
                <td>
                    <span class="stripe-badge <?= $p['status'] === 'active' ? 'stripe-badge-success' : 'stripe-badge-muted' ?>">
                        <?= htmlspecialchars($p['status']) ?>
                    </span>
                </td>
                <td class="text-end">
                    <a href="<?= BASE_URL ?>/product/edit/<?= (int) $p['product_id'] ?>" class="btn btn-ghost btn-sm">Sửa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
