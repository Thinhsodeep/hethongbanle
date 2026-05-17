<?php $isEdit = !empty($product); ?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0"><?= $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></h1>
        <p class="subtext mb-0">Thông tin sản phẩm</p>
    </div>
</div>
<div class="stripe-card" style="max-width:800px">
    <form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/product/<?= $isEdit ? 'update/' . (int) $product['product_id'] : 'store' ?>">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="label-text mb-1">Tên sản phẩm</label>
                <input type="text" name="name" class="form-control stripe-input" required value="<?= htmlspecialchars($product['name'] ?? '') ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="label-text mb-1">SKU</label>
                <input type="text" name="sku" class="form-control stripe-input" required value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="label-text mb-1">Danh mục</label>
                <select name="category_id" class="form-select stripe-input" required>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['category_id'] ?>" <?= (int)($product['category_id'] ?? 0) === (int)$c['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="label-text mb-1">Barcode</label>
                <input type="text" name="barcode" class="form-control stripe-input" value="<?= htmlspecialchars($product['barcode'] ?? '') ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="label-text mb-1">Đơn vị</label>
                <input type="text" name="unit" class="form-control stripe-input" value="<?= htmlspecialchars($product['unit'] ?? 'cái') ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="label-text mb-1">Giá bán</label>
                <input type="number" step="0.01" name="sell_price" class="form-control stripe-input" required value="<?= htmlspecialchars((string) ($product['sell_price'] ?? '0')) ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="label-text mb-1">Giá nhập</label>
                <input type="number" step="0.01" name="import_price" class="form-control stripe-input" value="<?= htmlspecialchars((string) ($product['import_price'] ?? '0')) ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="label-text mb-1">Trạng thái</label>
                <select name="status" class="form-select stripe-input">
                    <option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>active</option>
                    <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>inactive</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="label-text mb-1">Mô tả</label>
            <textarea name="description" class="form-control stripe-input" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label class="label-text mb-1">Ảnh sản phẩm</label>
            <input type="file" name="image" class="form-control stripe-input" accept="image/*">
            <?php if (!empty($product['image'])): ?>
            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($product['image']) ?>" alt="" class="mt-2" style="max-height:80px;border-radius:var(--radius-md)">
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Lưu</button>
            <a href="<?= BASE_URL ?>/product/index" class="btn btn-ghost">Hủy</a>
        </div>
    </form>
</div>
