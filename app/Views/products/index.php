<h1 class="h3 mb-4">Sản phẩm</h1>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="<?= BASE_URL ?>/product/create" class="btn btn-primary">Thêm sản phẩm</a>
    <a href="<?= BASE_URL ?>/product/categories" class="btn btn-outline-secondary">Danh mục</a>
</div>
<form class="row g-2 mb-3" method="get" action="<?= BASE_URL ?>/product/index">
    <div class="col-auto">
        <input type="text" name="kw" class="form-control" placeholder="Tìm SKU/tên..." value="<?= htmlspecialchars($kw ?? '') ?>">
    </div>
    <div class="col-auto">
        <select name="cat" class="form-select">
            <option value="0">Tất cả danh mục</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['category_id'] ?>" <?= ($cat ?? 0) === (int)$c['category_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-secondary">Lọc</button>
    </div>
</form>
<table class="table table-hover table-bordered align-middle">
    <thead class="table-light">
        <tr>
            <th>SKU</th>
            <th>Tên</th>
            <th>Danh mục</th>
            <th>Giá bán</th>
            <th>Trạng thái</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['sku']) ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['category_name']) ?></td>
            <td><?= number_format((float) $p['sell_price'], 0, ',', '.') ?> ₫</td>
            <td><span class="badge bg-<?= $p['status'] === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($p['status']) ?></span></td>
            <td>
                <a href="<?= BASE_URL ?>/product/edit/<?= (int) $p['product_id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
