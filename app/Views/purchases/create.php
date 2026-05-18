<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Tạo đơn nhập hàng</h1>
        <p class="subtext mb-0">Chọn nhà cung cấp và các biến thể sản phẩm cần nhập</p>
    </div>
    <a href="<?= BASE_URL ?>/purchase/index" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>

<form method="post" action="<?= BASE_URL ?>/purchase/create" id="poForm">
<div class="row g-4">
    <!-- CỘT TRÁI: Thông tin đơn -->
    <div class="col-md-4">
        <div class="stripe-card">
            <h3 class="h3 mb-3">Thông tin đơn nhập</h3>

            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <div class="mb-3">
                <label class="label-text mb-1">Chi nhánh <span class="text-danger">*</span></label>
                <select name="branch_id" class="form-select stripe-input" required>
                    <option value="">— Chọn chi nhánh —</option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?= (int) $b['branch_id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
            <input type="hidden" name="branch_id" value="<?= (int) $myBranchId ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="label-text mb-1">Nhà cung cấp <span class="text-danger">*</span></label>
                <select name="supplier_id" class="form-select stripe-input" required>
                    <option value="">— Chọn NCC —</option>
                    <?php foreach ($suppliers as $s): ?>
                    <option value="<?= (int) $s['supplier_id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="label-text mb-1">Ghi chú</label>
                <textarea name="note" class="form-control stripe-input" rows="3" placeholder="Ghi chú đơn nhập..."></textarea>
            </div>
        </div>

        <div class="stripe-card mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <span class="label-text">Tổng tiền đơn nhập</span>
                <span class="fw-700 fs-5" id="grandTotal">0đ</span>
            </div>
        </div>
    </div>

    <!-- CỘT PHẢI: Sản phẩm nhập -->
    <div class="col-md-8">
        <div class="stripe-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h3 mb-0">Danh sách sản phẩm nhập</h3>
                <button type="button" id="addRowBtn" class="btn btn-ghost btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Thêm dòng
                </button>
            </div>
            <table class="table stripe-table mb-0" id="itemTable">
                <thead>
                    <tr>
                        <th>Sản phẩm / Biến thể</th>
                        <th style="width:110px">Số lượng</th>
                        <th style="width:150px">Đơn giá nhập (đ)</th>
                        <th style="width:130px" class="text-end">Thành tiền</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody id="itemBody"></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3 gap-2">
            <a href="<?= BASE_URL ?>/purchase/index" class="btn btn-ghost">Hủy</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send me-1"></i>Tạo đơn nhập
            </button>
        </div>
    </div>
</div>
</form>

<script>
// variants = mảng từ Product::search() — mỗi phần tử là 1 variant v2.0
const variants = <?= json_encode(array_map(fn($v) => [
    'variant_id'   => $v['variant_id'],
    'product_name' => $v['product_name'],
    'sku'          => $v['sku'],
    'color'        => $v['color'] ?? '',
    'size'         => $v['size']  ?? '',
    'attribute'    => $v['attribute'] ?? '',
    'import_price' => $v['import_price'] ?? 0,
], $variants), JSON_UNESCAPED_UNICODE) ?>;

const variantOpts = variants.map(v => {
    const attrs = [v.color, v.size, v.attribute].filter(Boolean).join(' / ');
    const label = `${v.product_name} — ${v.sku}${attrs ? ' (' + attrs + ')' : ''}`;
    return `<option value="${v.variant_id}" data-price="${v.import_price}">${label}</option>`;
}).join('');

function calcTotal() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(tr => {
        const qty   = parseFloat(tr.querySelector('.qty-input').value)   || 0;
        const price = parseFloat(tr.querySelector('.price-input').value) || 0;
        const sub   = qty * price;
        tr.querySelector('.subtotal').textContent = sub.toLocaleString('vi-VN') + 'đ';
        total += sub;
    });
    document.getElementById('grandTotal').textContent = total.toLocaleString('vi-VN') + 'đ';
}

function addRow() {
    const tbody = document.getElementById('itemBody');
    const tr    = document.createElement('tr');
    tr.className = 'item-row';
    tr.innerHTML = `
        <td>
            <select name="variant_id[]" class="form-select stripe-input variant-sel" required>
                <option value="">— Chọn sản phẩm/biến thể —</option>
                ${variantOpts}
            </select>
        </td>
        <td><input type="number" name="quantity[]"   class="form-control stripe-input qty-input"   min="1" value="1"   required></td>
        <td><input type="number" name="unit_price[]" class="form-control stripe-input price-input" min="0" step="100" value="0" required></td>
        <td class="text-end subtext subtotal">0đ</td>
        <td><button type="button" class="btn btn-ghost btn-sm text-danger remove-row"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(tr);

    tr.querySelector('.variant-sel').addEventListener('change', function () {
        const price = this.selectedOptions[0]?.dataset?.price || 0;
        tr.querySelector('.price-input').value = price;
        calcTotal();
    });
    tr.querySelector('.qty-input').addEventListener('input', calcTotal);
    tr.querySelector('.price-input').addEventListener('input', calcTotal);
    tr.querySelector('.remove-row').addEventListener('click', () => {
        tr.remove();
        calcTotal();
        if (!document.getElementById('itemBody').rows.length) addRow();
    });
}

document.getElementById('addRowBtn').addEventListener('click', addRow);
addRow(); // Thêm 1 dòng mặc định
</script>
