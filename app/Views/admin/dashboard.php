<?php $s = $summary ?? []; ?>
<div class="stripe-page-header">
    <div>
        <h1 class="h1 mb-0">Dashboard</h1>
        <p class="subtext mb-0">Tổng quan tồn kho và hệ thống</p>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stripe-card">
            <p class="label-text mb-2">Chi nhánh active</p>
            <div class="h1 mb-0" style="color:var(--color-accent)"><?= (int) ($s['total_branches'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stripe-card">
            <p class="label-text mb-2">Sản phẩm active</p>
            <div class="h1 mb-0" style="color:var(--color-accent)"><?= (int) ($s['total_products'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stripe-card">
            <p class="label-text mb-2">Tổng tồn kho</p>
            <div class="h1 mb-0" style="color:var(--color-accent)"><?= number_format((int) ($s['total_stock'] ?? 0), 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stripe-card">
            <p class="label-text mb-2" style="color:var(--color-warning)">Sắp hết / Hết hàng</p>
            <div class="h1 mb-0" style="color:var(--color-warning)"><?= (int) ($s['low_stock_count'] ?? 0) ?> / <?= (int) ($s['out_of_stock_count'] ?? 0) ?></div>
        </div>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="stripe-card">
            <h5 class="mb-4">Doanh thu 7 ngày gần nhất</h5>
            <canvas id="revenueChart" height="80"></canvas>
        </div>
    </div>
</div>
<a href="<?= BASE_URL ?>/inventory/alerts" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-exclamation-triangle me-1"></i> Xem cảnh báo tồn kho
</a>

<?php if ($_SESSION['role'] === 'admin'): ?>
<div class="row g-3 mt-4 mb-4">
    <div class="col-md-6">
        <div class="stripe-card h-100">
            <h5 class="mb-3"><i class="bi bi-qr-code-scan me-2 text-primary"></i>Cấu hình thanh toán VietQR</h5>
            <p class="subtext mb-4">Thông tin ngân hàng dùng để tạo mã QR thanh toán động cho khách hàng khi mua sắm tại quầy.</p>
            <form action="<?= BASE_URL ?>/admin/updatePaymentSettings" method="POST">
                <div class="mb-3">
                    <label class="label-text mb-1">Ngân hàng</label>
                    <select name="bank_id" class="form-select stripe-input">
                        <option value="MB" <?= BANK_ID === 'MB' ? 'selected' : '' ?>>MBBank (Ngân hàng Quân Đội)</option>
                        <option value="VCB" <?= BANK_ID === 'VCB' ? 'selected' : '' ?>>Vietcombank (Ngân hàng Ngoại thương)</option>
                        <option value="ICB" <?= BANK_ID === 'ICB' ? 'selected' : '' ?>>VietinBank (Ngân hàng Công thương)</option>
                        <option value="BIDV" <?= BANK_ID === 'BIDV' ? 'selected' : '' ?>>BIDV (Ngân hàng Đầu tư & Phát triển)</option>
                        <option value="TCB" <?= BANK_ID === 'TCB' ? 'selected' : '' ?>>Techcombank (Ngân hàng Kỹ thương)</option>
                        <option value="ACB" <?= BANK_ID === 'ACB' ? 'selected' : '' ?>>ACB (Ngân hàng Á Châu)</option>
                        <option value="VPB" <?= BANK_ID === 'VPB' ? 'selected' : '' ?>>VPBank (Ngân hàng Thịnh Vượng)</option>
                        <option value="TPB" <?= BANK_ID === 'TPB' ? 'selected' : '' ?>>TPBank (Ngân hàng Tiên Phong)</option>
                        <option value="VIB" <?= BANK_ID === 'VIB' ? 'selected' : '' ?>>VIB (Ngân hàng Quốc tế)</option>
                        <option value="MSB" <?= BANK_ID === 'MSB' ? 'selected' : '' ?>>MSB (Ngân hàng Hàng Hải)</option>
                        <option value="Agribank" <?= BANK_ID === 'Agribank' ? 'selected' : '' ?>>Agribank (Ngân hàng Nông nghiệp)</option>
                        <option value="VCCB" <?= BANK_ID === 'VCCB' ? 'selected' : '' ?>>Timo (Ngân hàng số Timo / BVBank)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="label-text mb-1">Số tài khoản</label>
                    <input type="text" name="bank_account_no" class="form-control stripe-input" required value="<?= htmlspecialchars(BANK_ACCOUNT_NO) ?>" placeholder="Ví dụ: 0901111001">
                </div>
                <div class="mb-3">
                    <label class="label-text mb-1">Tên chủ tài khoản (Không dấu)</label>
                    <input type="text" name="bank_account_name" class="form-control stripe-input" required value="<?= htmlspecialchars(BANK_ACCOUNT_NAME) ?>" placeholder="Ví dụ: NGUYEN VAN A">
                </div>
                <div class="mb-3">
                    <label class="label-text mb-1">PayOS Client ID (Nhận tiền tự động)</label>
                    <input type="password" name="payos_client_id" class="form-control stripe-input" value="<?= htmlspecialchars(PAYOS_CLIENT_ID) ?>" placeholder="Nhập Client ID từ PayOS">
                </div>
                <div class="mb-3">
                    <label class="label-text mb-1">PayOS API Key</label>
                    <input type="password" name="payos_api_key" class="form-control stripe-input" value="<?= htmlspecialchars(PAYOS_API_KEY) ?>" placeholder="Nhập API Key từ PayOS">
                </div>
                <div class="mb-3">
                    <label class="label-text mb-1">PayOS Checksum Key</label>
                    <input type="password" name="payos_checksum_key" class="form-control stripe-input" value="<?= htmlspecialchars(PAYOS_CHECKSUM_KEY) ?>" placeholder="Nhập Checksum Key từ PayOS">
                    <span class="subtext text-muted" style="font-size: 0.75rem">Tạo cổng thanh toán miễn phí trên <a href="https://payos.vn" target="_blank">payos.vn</a> để kết nối tài khoản ngân hàng thực tế.</span>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-2"></i>Lưu cấu hình</button>
            </form>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stripe-card h-100 d-flex flex-column justify-content-between">
            <div>
                <h5 class="mb-3"><i class="bi bi-phone me-2 text-success"></i>Bản xem trước QR Code</h5>
                <p class="subtext">Demo quét thử mã QR của cửa hàng với số tiền mặc định 50.000đ.</p>
                <div class="text-center py-3">
                    <img src="https://img.vietqr.io/image/<?= BANK_ID ?>-<?= BANK_ACCOUNT_NO ?>-compact2.png?amount=50000&addInfo=DEMO%20THANH%20TOAN&accountName=<?= rawurlencode(BANK_ACCOUNT_NAME) ?>" alt="VietQR Demo" class="img-fluid rounded border" style="max-height: 200px; box-shadow: 0 4px 10px rgba(0,0,0,0.1)">
                </div>
            </div>
            <div class="subtext text-center mt-2">
                Quét mã trên bằng bất kỳ ứng dụng ngân hàng hoặc ví điện tử nào để kiểm tra tính năng.
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = <?= json_encode($revenue ?? []) ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(revenueData).map(d => d.split('-').slice(1).join('/')),
            datasets: [{
                label: 'Doanh thu (VND)',
                data: Object.values(revenueData),
                borderColor: '#635BFF',
                backgroundColor: 'rgba(99, 91, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
