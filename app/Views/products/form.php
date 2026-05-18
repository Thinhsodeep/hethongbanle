<?php $isEdit = !empty($product); ?>
<style>
.variant-table th, .variant-table td { vertical-align: middle; }
.variant-table input { font-size: .85rem; }
.variant-table select { font-size: .85rem; }
.del-variant-btn:hover { color: var(--color-danger) !important; }
</style>

<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0"><?= $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></h1>
        <p class="subtext mb-0"><?= $isEdit ? htmlspecialchars($product['name']) : 'Điền thông tin sản phẩm và các biến thể (SKU)' ?></p>
    </div>
    <a href="<?= BASE_URL ?>/product/index" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>

<form method="post" enctype="multipart/form-data"
      action="<?= BASE_URL ?>/product/<?= $isEdit ? 'update/' . (int) $product['product_id'] : 'store' ?>">

<div class="row g-4">
    <!-- CỘT TRÁI: Thông tin chung -->
    <div class="col-lg-4">
        <div class="stripe-card">
            <h3 class="h3 mb-4">Thông tin chung</h3>

            <div class="mb-3">
                <label class="label-text mb-1">Tên sản phẩm <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control stripe-input" required
                       value="<?= htmlspecialchars($product['name'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="label-text mb-1">Danh mục <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select stripe-input" required>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['category_id'] ?>"
                        <?= (int)($product['category_id'] ?? 0) === (int)$c['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="label-text mb-1">Đơn vị tính</label>
                <input type="text" name="unit" class="form-control stripe-input"
                       value="<?= htmlspecialchars($product['unit'] ?? 'cái') ?>" placeholder="cái, hộp, kg...">
            </div>

            <?php if ($isEdit): ?>
            <div class="mb-3">
                <label class="label-text mb-1">Trạng thái sản phẩm</label>
                <select name="status" class="form-select stripe-input">
                    <option value="active"   <?= ($product['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Đang bán</option>
                    <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Ẩn</option>
                </select>
            </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="label-text mb-1">Mô tả</label>
                <textarea name="description" class="form-control stripe-input" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label class="label-text mb-1">Ảnh sản phẩm</label>
                <input type="file" name="image" class="form-control stripe-input" accept="image/*">
                <?php if (!empty($product['image'])): ?>
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($product['image']) ?>" alt=""
                     class="mt-2 rounded" style="max-height:100px;border:1px solid var(--border)">
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Lưu thay đổi' : 'Tạo sản phẩm' ?>
            </button>
        </div>
    </div>

    <!-- CỘT PHẢI: Quản lý Variants -->
    <div class="col-lg-8">
        <div class="stripe-card p-0">
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                <div>
                    <h3 class="h3 mb-0">Biến thể / SKU</h3>
                    <p class="subtext mb-0" style="font-size:.8rem">Mỗi dòng là 1 SKU (màu sắc / kích cỡ / thuộc tính khác)</p>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" id="addVariantBtn">
                    <i class="bi bi-plus-lg me-1"></i>Thêm dòng
                </button>
            </div>

            <div class="table-responsive">
            <table class="table variant-table mb-0" id="variantTable">
                <thead style="font-size:.8rem">
                    <tr>
                        <th>SKU <span class="text-danger">*</span></th>
                        <th>Barcode</th>
                        <th>Màu</th>
                        <th>Size</th>
                        <th>Thuộc tính</th>
                        <th>Giá bán</th>
                        <th>Giá nhập</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="existingVariants">
                    <?php if ($isEdit): ?>
                    <?php foreach ($variants as $v): ?>
                    <tr class="existing-variant-row">
                        <td>
                            <input type="hidden" name="variant_id[]" value="<?= (int) $v['variant_id'] ?>">
                            <input type="text" name="sku[]" class="form-control stripe-input"
                                   value="<?= htmlspecialchars($v['sku']) ?>" required style="min-width:100px">
                        </td>
                        <td><input type="text" name="barcode[]" class="form-control stripe-input"
                                   value="<?= htmlspecialchars($v['barcode'] ?? '') ?>" style="min-width:90px"></td>
                        <td><input type="text" name="color[]" class="form-control stripe-input"
                                   value="<?= htmlspecialchars($v['color'] ?? '') ?>" placeholder="Đỏ" style="min-width:70px"></td>
                        <td><input type="text" name="size[]" class="form-control stripe-input"
                                   value="<?= htmlspecialchars($v['size'] ?? '') ?>" placeholder="M" style="min-width:60px"></td>
                        <td><input type="text" name="attribute[]" class="form-control stripe-input"
                                   value="<?= htmlspecialchars($v['attribute'] ?? '') ?>" style="min-width:80px"></td>
                        <td><input type="number" name="sell_price[]" class="form-control stripe-input"
                                   value="<?= (float) $v['sell_price'] ?>" step="1000" min="0" style="min-width:100px"></td>
                        <td><input type="number" name="import_price[]" class="form-control stripe-input"
                                   value="<?= (float) $v['import_price'] ?>" step="1000" min="0" style="min-width:100px"></td>
                        <td>
                            <select name="vstatus[]" class="form-select stripe-input" style="min-width:90px">
                                <option value="active"   <?= ($v['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Bán</option>
                                <option value="inactive" <?= ($v['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Ẩn</option>
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn btn-ghost btn-sm del-variant-btn text-muted"
                                    data-vid="<?= (int) $v['variant_id'] ?>"
                                    data-pid="<?= (int) $product['product_id'] ?>"
                                    title="Xóa biến thể">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tbody id="newVariants">
                    <?php if (!$isEdit): ?>
                    <!-- Thêm 1 dòng trắng mặc định khi tạo mới -->
                    <tr class="new-variant-row">
                        <td><input type="text" name="sku[]"          class="form-control stripe-input" required style="min-width:100px"></td>
                        <td><input type="text" name="barcode[]"      class="form-control stripe-input" style="min-width:90px"></td>
                        <td><input type="text" name="color[]"        class="form-control stripe-input" placeholder="Đỏ" style="min-width:70px"></td>
                        <td><input type="text" name="size[]"         class="form-control stripe-input" placeholder="M" style="min-width:60px"></td>
                        <td><input type="text" name="attribute[]"    class="form-control stripe-input" style="min-width:80px"></td>
                        <td><input type="number" name="sell_price[]" class="form-control stripe-input" value="0" step="1000" min="0" style="min-width:100px"></td>
                        <td><input type="number" name="import_price[]" class="form-control stripe-input" value="0" step="1000" min="0" style="min-width:100px"></td>
                        <td>
                            <select name="vstatus[]" class="form-select stripe-input" style="min-width:90px">
                                <option value="active">Bán</option>
                                <option value="inactive">Ẩn</option>
                            </select>
                        </td>
                        <td><button type="button" class="btn btn-ghost btn-sm text-muted remove-new-row"><i class="bi bi-x-lg"></i></button></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>

            <?php if ($isEdit && !empty($variants)): ?>
            <div class="px-4 py-3 border-top">
                <p class="subtext mb-2" style="font-size:.8rem"><i class="bi bi-info-circle me-1"></i>Dòng bên trên là các SKU hiện có. Nhấn <strong>Thêm dòng</strong> để tạo SKU mới.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</form>

<!-- Hidden form để xóa variant -->
<form id="deleteVariantForm" method="post" style="display:none">
    <input type="hidden" name="product_id" id="dvProductId" value="">
</form>

<script>
// ─── Template dòng variant MỚI (dùng new_* để phân biệt với existing) ────
function newRowHtml() {
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    const prefix = isEdit ? 'new_' : '';
    return `<tr class="new-variant-row">
        <td><input type="text" name="${prefix}sku[]"          class="form-control stripe-input" required style="min-width:100px"></td>
        <td><input type="text" name="${prefix}barcode[]"      class="form-control stripe-input" style="min-width:90px"></td>
        <td><input type="text" name="${prefix}color[]"        class="form-control stripe-input" placeholder="Đỏ" style="min-width:70px"></td>
        <td><input type="text" name="${prefix}size[]"         class="form-control stripe-input" placeholder="M" style="min-width:60px"></td>
        <td><input type="text" name="${prefix}attribute[]"    class="form-control stripe-input" style="min-width:80px"></td>
        <td><input type="number" name="${prefix}sell_price[]" class="form-control stripe-input" value="0" step="1000" min="0" style="min-width:100px"></td>
        <td><input type="number" name="${prefix}import_price[]" class="form-control stripe-input" value="0" step="1000" min="0" style="min-width:100px"></td>
        <td>
            <select name="${prefix}vstatus[]" class="form-select stripe-input" style="min-width:90px">
                <option value="active">Bán</option>
                <option value="inactive">Ẩn</option>
            </select>
        </td>
        <td><button type="button" class="btn btn-ghost btn-sm text-muted remove-new-row"><i class="bi bi-x-lg"></i></button></td>
    </tr>`;
}

document.getElementById('addVariantBtn').addEventListener('click', () => {
    document.getElementById('newVariants').insertAdjacentHTML('beforeend', newRowHtml());
});

document.addEventListener('click', (e) => {
    // Xóa dòng mới (chưa lưu)
    if (e.target.closest('.remove-new-row')) {
        e.target.closest('tr').remove();
    }

    // Xóa variant đã có trong DB
    if (e.target.closest('.del-variant-btn')) {
        const btn = e.target.closest('.del-variant-btn');
        const vid = btn.dataset.vid;
        const pid = btn.dataset.pid;
        if (!confirm(`Xóa biến thể này? Tồn kho liên quan cũng sẽ bị xóa!`)) return;

        const form = document.getElementById('deleteVariantForm');
        form.action = `<?= BASE_URL ?>/product/deleteVariant/${vid}`;
        document.getElementById('dvProductId').value = pid;
        form.submit();
    }
});
</script>
