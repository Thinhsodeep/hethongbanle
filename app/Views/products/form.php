<?php $isEdit = !empty($product); ?>
<h1 class="h3 mb-4"><?= $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></h1>
<form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/product/<?= $isEdit ? 'update/' . (int) $product['product_id'] : 'store' ?>">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($product['name'] ?? '') ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">SKU</label>
            <input type="text" name="sku" class="form-control" required value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Danh mục</label>
            <select name="category_id" class="form-select" required>
                <?php foreach ($categories as $c): ?>
                <option value="<?= (int) $c['category_id'] ?>" <?= (int)($product['category_id'] ?? 0) === (int)$c['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Barcode</label>
            <input type="text" name="barcode" class="form-control" value="<?= htmlspecialchars($product['barcode'] ?? '') ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Đơn vị</label>
            <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($product['unit'] ?? 'cái') ?>">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Giá bán</label>
            <input type="number" step="0.01" name="sell_price" class="form-control" required value="<?= htmlspecialchars((string) ($product['sell_price'] ?? '0')) ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Giá nhập</label>
            <input type="number" step="0.01" name="import_price" class="form-control" value="<?= htmlspecialchars((string) ($product['import_price'] ?? '0')) ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>active</option>
                <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>inactive</option>
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Mô tả</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Ảnh sản phẩm</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        <?php if (!empty($product['image'])): ?>
        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($product['image']) ?>" alt="" class="mt-2" style="max-height:80px">
        <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">Lưu</button>
    <a href="<?= BASE_URL ?>/product/index" class="btn btn-secondary">Hủy</a>
</form>
