<style>
.pos-layout { display:grid; grid-template-columns:1fr 380px; gap:1.5rem; height:calc(100vh - 120px); }
.pos-left  { overflow-y:auto; }
.pos-right { display:flex; flex-direction:column; overflow:hidden; }
.pos-cart  { flex:1; overflow-y:auto; }
.product-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:.75rem; }
.product-card {
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:var(--radius-md);
    padding:.75rem;
    cursor:pointer;
    transition:all .15s;
}
.product-card:hover { border-color:var(--brand); background:var(--surface-hover); }
.product-card.out-of-stock { opacity:.5; cursor:not-allowed; }
.cart-item { display:flex; align-items:center; gap:.5rem; padding:.5rem 0; border-bottom:1px solid var(--border); }
.qty-btn { width:28px;height:28px;border-radius:50%;border:1px solid var(--border);background:transparent;
           cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center; }
.qty-btn:hover { background:var(--surface-hover); }
</style>

<div class="pos-layout">
<!-- LEFT: Find + Products -->
<div class="pos-left">
    <div class="mb-3 d-flex gap-2">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
            <input type="text" id="searchInput" class="form-control stripe-input"
                   placeholder="Tim ten / SKU / barcode..." autofocus>
        </div>
        <select id="catFilter" class="form-select stripe-input" style="max-width:180px">
            <option value="">Tat ca danh muc</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= (int) $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="product-grid" id="productGrid">
        <div class="text-center subtext py-5 w-100" style="grid-column:1/-1">
            Nhap tu khoa hoac chon danh muc de tim san pham.
        </div>
    </div>
</div>

<!-- RIGHT: Cart + Checkout -->
<div class="pos-right stripe-card p-0 d-flex flex-column" style="position:sticky;top:0">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h3 mb-0"><i class="bi bi-cart3 me-2"></i>Gio hang</h3>
        <button class="btn btn-ghost btn-sm text-danger" id="clearCart">Xoa tat ca</button>
    </div>

    <div class="pos-cart px-3" id="cartList">
        <div class="text-center subtext py-4" id="cartEmpty">Chua co san pham.</div>
    </div>

    <div class="p-3 border-top border-bottom">
        <div class="d-flex gap-2">
            <input type="text" id="customerPhone" class="form-control stripe-input"
                   placeholder="SDT khach hang" style="flex:1">
            <button class="btn btn-ghost btn-sm" id="findCustomerBtn">Tim</button>
        </div>
        <div id="customerInfo" class="mt-2" style="display:none">
            <div class="d-flex justify-content-between align-items-center p-2 rounded" style="background:var(--surface-hover)">
                <div>
                    <div class="fw-500" id="customerName"></div>
                    <div class="subtext" style="font-size:.8rem">
                        <i class="bi bi-star-fill text-warning me-1"></i><span id="customerPoints"></span> diem
                    </div>
                </div>
                <button class="btn btn-ghost btn-sm" id="removeCustomer"><i class="bi bi-x"></i></button>
            </div>
        </div>
    </div>

    <div class="p-3 border-bottom">
        <div class="d-flex justify-content-between mb-2">
            <span class="subtext">Tong tien hang</span>
            <span id="subtotalDisplay" class="fw-500">0d</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="subtext">Giam gia (d)</span>
            <input type="number" id="discountInput" class="form-control stripe-input text-end"
                   style="width:130px" min="0" step="1000" value="0">
        </div>
        <div class="d-flex justify-content-between fw-700" style="font-size:1.1rem">
            <span>Thuc thu</span>
            <span id="finalDisplay" class="text-primary">0d</span>
        </div>
    </div>

    <div class="p-3 border-bottom">
        <div class="label-text mb-2">Hinh thuc thanh toan</div>
        <div class="d-flex gap-2">
            <label class="d-flex align-items-center gap-1 px-2 py-1 rounded border" style="cursor:pointer;flex:1;justify-content:center">
                <input type="radio" name="payMethod" value="cash" checked> Tiền mặt
            </label>
            <label class="d-flex align-items-center gap-1 px-2 py-1 rounded border" style="cursor:pointer;flex:1;justify-content:center">
                <input type="radio" name="payMethod" value="card"> Quẹt thẻ
            </label>
            <label class="d-flex align-items-center gap-1 px-2 py-1 rounded border" style="cursor:pointer;flex:1;justify-content:center">
                <input type="radio" name="payMethod" value="transfer"> VietQR (Online)
            </label>
        </div>
    </div>

    <div class="p-3">
        <button class="btn btn-primary w-100 py-2" id="checkoutBtn" disabled style="font-size:1rem">
            <i class="bi bi-check-circle me-2"></i>Thanh toán
        </button>
        <a href="<?= BASE_URL ?>/pos/history" class="btn btn-ghost btn-sm w-100 mt-2">
            <i class="bi bi-clock-history me-1"></i>Lịch sử đơn
        </a>
    </div>
</div>
</div>

<!-- VietQR Payment Modal -->
<div class="modal fade" id="vietqrModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content stripe-card p-0" style="border-radius:var(--radius-lg); overflow:hidden">
            <div class="modal-header border-bottom px-4 py-3" style="background:var(--color-primary); color:white">
                <h5 class="modal-title d-flex align-items-center"><i class="bi bi-qr-code-scan me-2 text-info"></i> Thanh toán Online qua VietQR</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="closeVietQRBtn"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <p class="subtext mb-1">Tổng tiền cần thanh toán</p>
                    <h2 class="text-primary fw-700" id="qrAmountDisplay" style="font-size:1.8rem; margin:0">0đ</h2>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6 text-center d-flex flex-column align-items-center justify-content-center">
                        <div class="position-relative p-2 border rounded" style="background:#fff">
                            <img id="vietqrImage" src="" alt="Mã VietQR" style="width:180px; height:180px; object-fit:contain">
                            <div id="qrExpiredOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column align-items-center justify-content-center bg-white bg-opacity-95">
                                <i class="bi bi-exclamation-circle text-danger" style="font-size:2.5rem"></i>
                                <span class="fw-500 text-danger mt-1">Mã đã hết hạn</span>
                                <button class="btn btn-sm btn-outline-primary mt-2" id="btnRegenQR">Tạo lại mã</button>
                            </div>
                        </div>
                        <div class="mt-2 text-muted" style="font-size:0.75rem">
                            Quét bằng ứng dụng Ngân hàng hoặc Ví điện tử (MoMo, ZaloPay, ...)
                        </div>
                        <div id="payLinkContainer" style="display:none" class="w-100 mt-2"></div>
                    </div>
                    <div class="col-md-6 d-flex flex-column justify-content-between">
                        <div class="p-3 rounded" style="background:var(--color-neutral); font-size:0.85rem">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Ngân hàng:</span>
                                <strong class="text-end" id="qrBankDisplay">MB</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Số tài khoản:</span>
                                <strong class="text-end" id="qrAccDisplay">0901111001</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Chủ tài khoản:</span>
                                <strong class="text-end" id="qrNameDisplay">NGUYEN VAN ADMIN</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Nội dung CK:</span>
                                <strong class="text-end text-danger" id="qrNoteDisplay">HDBL123</strong>
                            </div>
                        </div>
                        
                        <div class="my-3 text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                                <span class="fw-500 text-secondary" id="qrStatusText">Đang chờ khách thanh toán...</span>
                            </div>
                            <span class="subtext text-muted" style="font-size:0.8rem">Hiệu lực: <strong id="qrTimer">05:00</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top p-3 d-flex gap-2">
                <button class="btn btn-ghost flex-grow-1" id="btnCancelQR">Hủy giao dịch</button>
                <button class="btn btn-secondary" id="btnSimulateSuccess"><i class="bi bi-patch-check me-1"></i> Giả lập thành công</button>
                <button class="btn btn-primary flex-grow-1" id="btnVerifyQR"><i class="bi bi-check-circle me-1 text-white"></i> Xác nhận đã nhận tiền</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content stripe-card p-0" style="border-radius:var(--radius-lg)">
            <div class="modal-body p-4 text-center">
                <div style="font-size:3rem">&#10003;</div>
                <h3 class="h3 mt-2">Thanh toán thành công!</h3>
                <p class="subtext mb-3">Đơn hàng <strong id="successOrderId"></strong> đã được tạo.</p>
                <p class="subtext mb-4" id="loyaltyMsg" style="display:none"></p>
                <div class="d-flex gap-2 justify-content-center">
                    <a id="receiptLink" href="#" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-printer me-1"></i>In hóa đơn
                    </a>
                    <button class="btn btn-primary" id="newOrderBtn">Đơn mới</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.POS_CONFIG = {
    baseUrl:         '<?= BASE_URL ?>',
    searchUrl:       '<?= BASE_URL ?>/pos/search',
    findCustomerUrl: '<?= BASE_URL ?>/pos/findCustomer',
    storeUrl:        '<?= BASE_URL ?>/pos/store',
    cancelOrderUrl:  '<?= BASE_URL ?>/pos/cancelOrder',
    checkPaymentUrl: '<?= BASE_URL ?>/pos/checkPayment',
    receiptBase:     '<?= BASE_URL ?>/pos/receipt/',
    bankId:          '<?= BANK_ID ?>',
    bankAccountNo:   '<?= BANK_ACCOUNT_NO ?>',
    bankAccountName: '<?= BANK_ACCOUNT_NAME ?>',
};
</script>
<script src="<?= BASE_URL ?>/js/pos.js?v=<?= time() ?>"></script>

