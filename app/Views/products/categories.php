<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Danh mục sản phẩm</h1>
        <p class="subtext mb-0">Phân loại sản phẩm</p>
    </div>
    <a href="<?= BASE_URL ?>/product/index" class="btn btn-ghost">← Sản phẩm</a>
</div>
<div class="row g-3">
    <div class="col-md-5">
        <div class="stripe-card">
            <h2 class="h3 mb-3">Thêm danh mục</h2>
            <form method="post" action="<?= BASE_URL ?>/product/storeCategory">
                <div class="mb-3">
                    <label class="label-text mb-1">Tên</label>
                    <input type="text" name="name" class="form-control stripe-input" required>
                </div>
                <div class="mb-3">
                    <label class="label-text mb-1">Mô tả</label>
                    <textarea name="description" class="form-control stripe-input" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Thêm</button>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="stripe-card p-0">
            <table class="table stripe-table mb-0">
                <thead><tr><th>Tên</th><th>Mô tả</th></tr></thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                    <tr>
                        <td class="fw-500"><?= htmlspecialchars($c['name']) ?></td>
                        <td class="subtext"><?= htmlspecialchars($c['description'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
