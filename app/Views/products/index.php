<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Sản phẩm</h1>
        <p class="subtext mb-0">Quản lý toàn bộ sản phẩm trong hệ thống</p>
    </div>
    <div class="stripe-page-header-actions">
        <a href="<?= BASE_URL ?>/product/categories" class="btn btn-ghost">Danh mục</a>
        <a href="<?= BASE_URL ?>/productImport/index" class="btn btn-outline-primary"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Import Excel (AI)</a>
        <a href="<?= BASE_URL ?>/product/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Thêm sản phẩm</a>
    </div>
</div>

<div class="stripe-card p-0">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom flex-wrap gap-2">
        <p class="subtext mb-0">Mỗi sản phẩm có thể có nhiều biến thể (SKU / màu sắc / kích cỡ)</p>
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
                <th style="width:40%">Sản phẩm</th>
                <th>Danh mục</th>
                <th>Số biến thể</th>
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
                        <?php if (!empty($p['image'])): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($p['image']) ?>"
                                 style="width:40px;height:40px;object-fit:cover;border-radius:var(--radius-sm);border:1px solid var(--border)">
                        <?php else: ?>
                            <div class="stripe-avatar"><?= mb_strtoupper(mb_substr($p['name'], 0, 2)) ?></div>
                        <?php endif; ?>
                        <div>
                            <div class="fw-500"><?= htmlspecialchars($p['name']) ?></div>
                            <div class="subtext" style="font-size:.8rem">
                                <?= htmlspecialchars($p['unit'] ?? 'cái') ?>
                                <?php if (!empty($p['description'])): ?>
                                  · <span title="<?= htmlspecialchars($p['description']) ?>"><?= mb_strimwidth(htmlspecialchars($p['description']), 0, 40, '…') ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="subtext"><?= htmlspecialchars($p['category_name']) ?></td>
                <td>
                    <span class="badge bg-primary" style="font-size:.8rem"><?= (int) $p['variant_count'] ?> SKU</span>
                </td>
                <td>
                    <?php
                        $min = (float) ($p['min_price'] ?? 0);
                        $max = (float) ($p['max_price'] ?? 0);
                        if ($min === $max) {
                            echo number_format($min, 0, ',', '.') . ' ₫';
                        } else {
                            echo number_format($min, 0, ',', '.') . ' – ' . number_format($max, 0, ',', '.') . ' ₫';
                        }
                    ?>
                </td>
                <td>
                    <span class="stripe-badge <?= $p['status'] === 'active' ? 'stripe-badge-success' : 'stripe-badge-muted' ?>">
                        <?= $p['status'] === 'active' ? 'Đang bán' : 'Ẩn' ?>
                    </span>
                </td>
                <td class="text-end">
                    <a href="<?= BASE_URL ?>/product/edit/<?= (int) $p['product_id'] ?>" class="btn btn-ghost btn-sm">
                        <i class="bi bi-pencil me-1"></i>Sửa
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
            <tr><td colspan="6" class="text-center subtext py-5">Không tìm thấy sản phẩm.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
