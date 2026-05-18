<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Tạo phiếu chuyển kho</h1>
        <p class="subtext mb-0">Chọn chi nhánh và sản phẩm cần chuyển</p>
    </div>
    <a href="<?= BASE_URL ?>/transfer/index" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>

<form method="post" action="<?= BASE_URL ?>/transfer/create" id="transferForm">
<div class="row g-4">
    <!-- Bên trái: chọn chi nhánh -->
    <div class="col-md-4">
        <div class="stripe-card">
            <h3 class="h3 mb-3">Thông tin phiếu</h3>
            <div class="mb-3">
                <label class="label-text mb-1">Chi nhánh xuất <span class="text-danger">*</span></label>
                <select name="from_branch_id" id="fromBranch" class="form-select stripe-input" required>
                    <option value="">— Chọn chi nhánh xuất —</option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?= (int) $b['branch_id'] ?>"
                        <?= ($myBranchId && $myBranchId === (int)$b['branch_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="label-text mb-1">Chi nhánh nhận <span class="text-danger">*</span></label>
                <select name="to_branch_id" id="toBranch" class="form-select stripe-input" required>
                    <option value="">— Chọn chi nhánh nhận —</option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?= (int) $b['branch_id'] ?>">
                        <?= htmlspecialchars($b['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="label-text mb-1">Ghi chú</label>
                <textarea name="note" class="form-control stripe-input" rows="3" placeholder="Lý do chuyển kho..."></textarea>
            </div>
            <button type="button" id="loadProductsBtn" class="btn btn-outline-primary w-100">
                <i class="bi bi-box-seam me-1"></i>Tải danh sách sản phẩm
            </button>
        </div>
    </div>

    <!-- Bên phải: danh sách sản phẩm -->
    <div class="col-md-8">
        <div class="stripe-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h3 mb-0">Sản phẩm chuyển</h3>
                <button type="button" id="addRowBtn" class="btn btn-ghost btn-sm" style="display:none">
                    <i class="bi bi-plus-lg me-1"></i>Thêm dòng
                </button>
            </div>
            <div id="productArea">
                <p class="subtext text-center py-4" id="emptyMsg">Chọn chi nhánh xuất và nhấn "Tải danh sách sản phẩm".</p>
            </div>
            <table class="table stripe-table mb-0" id="itemTable" style="display:none">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th style="width:130px">Tồn kho</th>
                        <th style="width:130px">Số lượng</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody id="itemBody"></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3 gap-2">
            <a href="<?= BASE_URL ?>/transfer/index" class="btn btn-ghost">Hủy</a>
            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                <i class="bi bi-send me-1"></i>Tạo phiếu chuyển
            </button>
        </div>
    </div>
</div>
</form>

<script>
let productsData = [];

document.getElementById('loadProductsBtn').addEventListener('click', async function () {
    const fromId = document.getElementById('fromBranch').value;
    const toId   = document.getElementById('toBranch').value;
    if (!fromId) { alert('Vui lòng chọn chi nhánh xuất.'); return; }
    if (!toId)   { alert('Vui lòng chọn chi nhánh nhận.'); return; }
    if (fromId === toId) { alert('Chi nhánh xuất và nhận phải khác nhau.'); return; }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang tải...';

    const res  = await fetch(`<?= BASE_URL ?>/transfer/products?branch_id=${fromId}`);
    productsData = await res.json();

    this.disabled = false;
    this.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Tải lại';

    renderFirstRow();
});

function renderFirstRow() {
    document.getElementById('emptyMsg')?.remove();
    const tbody = document.getElementById('itemBody');
    tbody.innerHTML = '';
    document.getElementById('itemTable').style.display = '';
    document.getElementById('addRowBtn').style.display  = '';
    document.getElementById('submitBtn').disabled = false;
    addRow();
}

function addRow() {
    const tbody = document.getElementById('itemBody');
    const tr    = document.createElement('tr');
    const opts  = productsData.map(p =>
        `<option value="${p.variant_id}" data-stock="${p.quantity}">${p.product_name} (${p.sku}) — còn ${p.quantity}</option>`
    ).join('');
    tr.innerHTML = `
        <td>
            <select name="variant_id[]" class="form-select stripe-input product-select" required>
                <option value="">— Chọn sản phẩm —</option>
                ${opts}
            </select>
        </td>
        <td><span class="stock-info subtext">—</span></td>
        <td><input type="number" name="quantity[]" class="form-control stripe-input qty-input" min="1" value="1" required></td>
        <td><button type="button" class="btn btn-ghost btn-sm text-danger remove-row"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(tr);

    tr.querySelector('.product-select').addEventListener('change', function () {
        const opt   = this.selectedOptions[0];
        const stock = opt.dataset.stock || '—';
        tr.querySelector('.stock-info').textContent = stock ? `Tồn: ${stock}` : '—';
        tr.querySelector('.qty-input').max = stock;
    });
    tr.querySelector('.remove-row').addEventListener('click', () => {
        tr.remove();
        if (!document.getElementById('itemBody').rows.length) addRow();
    });
}

document.getElementById('addRowBtn').addEventListener('click', addRow);
</script>
