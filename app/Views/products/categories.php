<h1 class="h3 mb-4">Danh mục sản phẩm</h1>
<div class="row">
    <div class="col-md-5">
        <form method="post" action="<?= BASE_URL ?>/product/storeCategory" class="card card-body mb-4">
            <h2 class="h6">Thêm danh mục</h2>
            <div class="mb-3">
                <label class="form-label">Tên</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Thêm</button>
        </form>
    </div>
    <div class="col-md-7">
        <table class="table table-bordered align-middle">
            <thead class="table-light"><tr><th>Tên</th><th>Mô tả</th></tr></thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['description'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<p><a href="<?= BASE_URL ?>/product/index">← Về danh sách sản phẩm</a></p>
