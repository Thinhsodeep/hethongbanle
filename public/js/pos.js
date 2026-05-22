/**
 * pos.js — Xử lý logic màn hình POS (Module 7)
 * Phụ thuộc: window.POS_CONFIG phải được định nghĩa trong view
 */
(function () {
    'use strict';

    const cfg = window.POS_CONFIG || {};

    // ─── State ───────────────────────────────────────────────────────
    let cart     = [];  // [{ product_id, name, sku, unit, sell_price, quantity, stock }]
    let customer = null; // { customer_id, full_name, loyalty_points, ... }
    let searchTimer = null;
    let qrTimerInterval = null;
    let mockSuccessTimeout = null;
    let activeOrderId = null;
    let checkInterval = null;


    // ─── Elements ────────────────────────────────────────────────────
    const searchInput   = document.getElementById('searchInput');
    const catFilter     = document.getElementById('catFilter');
    const productGrid   = document.getElementById('productGrid');
    const cartList      = document.getElementById('cartList');
    const cartEmpty     = document.getElementById('cartEmpty');
    const subtotalEl    = document.getElementById('subtotalDisplay');
    const finalEl       = document.getElementById('finalDisplay');
    const discountInput = document.getElementById('discountInput');
    const checkoutBtn   = document.getElementById('checkoutBtn');
    const clearCartBtn  = document.getElementById('clearCart');
    const customerPhone = document.getElementById('customerPhone');
    const findCustBtn   = document.getElementById('findCustomerBtn');
    const custInfo      = document.getElementById('customerInfo');
    const custNameEl    = document.getElementById('customerName');
    const custPtsEl     = document.getElementById('customerPoints');
    const removeCustBtn = document.getElementById('removeCustomer');
    const newOrderBtn   = document.getElementById('newOrderBtn');
    const successOrderEl= document.getElementById('successOrderId');
    const receiptLink   = document.getElementById('receiptLink');
    const loyaltyMsgEl  = document.getElementById('loyaltyMsg');

    // ─── Tìm sản phẩm ────────────────────────────────────────────────
    async function searchProducts() {
        const q   = searchInput?.value.trim() || '';
        const cat = catFilter?.value || '';
        productGrid.innerHTML = '<div class="text-center subtext py-5 w-100" style="grid-column:1/-1"><span class="spinner-border spinner-border-sm me-2"></span>Đang tải...</div>';
        try {
            const res  = await fetch(`${cfg.searchUrl}?q=${encodeURIComponent(q)}&cat=${cat}`);
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                throw new Error('Server returned invalid JSON: ' + text.substring(0, 50));
            }
            if (!res.ok) throw new Error(data.message || 'Server error ' + res.status);
            renderProducts(data);
        } catch (e) {
            console.error('POS Search Error:', e);
            productGrid.innerHTML = `<div class="text-center subtext py-5 w-100 text-danger" style="grid-column:1/-1">Lỗi tải sản phẩm: ${e.message}. Vui lòng thử lại.</div>`;
        }
    }

    function renderProducts(products) {
        if (!products.length) {
            productGrid.innerHTML = '<div class="text-center subtext py-5 w-100" style="grid-column:1/-1">Không tìm thấy sản phẩm.</div>';
            return;
        }
        productGrid.innerHTML = products.map(p => {
            const outOfStock = p.stock <= 0;
            // Hiển thị thêm màu/size/thuộc tính nếu có
            const attrs = [p.color, p.size, p.attribute].filter(Boolean).join(' / ');
            
            const imgHtml = p.image 
                ? `<div style="height:100px; display:flex; align-items:center; justify-content:center; margin-bottom:8px; overflow:hidden; border-radius:var(--radius-sm)"><img src="${cfg.baseUrl}/${p.image}" style="max-height:100%; max-width:100%; object-fit:contain" alt=""></div>`
                : `<div style="height:100px; background:var(--surface-hover); display:flex; align-items:center; justify-content:center; margin-bottom:8px; border-radius:var(--radius-sm); color:var(--text-muted)"><i class="bi bi-box" style="font-size:2rem"></i></div>`;
                
            return `<div class="product-card ${outOfStock ? 'out-of-stock' : ''}"
                         data-variant-id="${p.variant_id}"
                         data-product-id="${p.product_id}"
                         data-name="${escHtml(p.product_name || p.name)}"
                         data-sku="${escHtml(p.sku)}"
                         data-unit="${escHtml(p.unit)}"
                         data-price="${p.sell_price}"
                         data-stock="${p.stock}"
                         data-image="${p.image || ''}"
                         data-attrs="${escHtml(attrs)}">
                ${imgHtml}
                <div class="fw-500" style="font-size:.85rem;line-height:1.3">${escHtml(p.product_name || p.name)}</div>
                <div class="subtext" style="font-size:.75rem">${escHtml(p.sku)}${attrs ? ' • ' + escHtml(attrs) : ''}</div>
                <div class="d-flex justify-content-between mt-1 align-items-center">
                    <span class="fw-700 text-primary" style="font-size:.9rem">${fmtMoney(p.sell_price)}</span>
                    <span class="badge ${outOfStock ? 'bg-danger' : 'bg-success'}" style="font-size:.7rem">
                        ${outOfStock ? 'Hết' : 'Còn ' + p.stock}
                    </span>
                </div>
            </div>`;
        }).join('');

        productGrid.querySelectorAll('.product-card:not(.out-of-stock)').forEach(card => {
            card.addEventListener('click', () => addToCart({
                variant_id: parseInt(card.dataset.variantId),
                product_id: parseInt(card.dataset.productId),
                name:       card.dataset.name,
                sku:        card.dataset.sku,
                unit:       card.dataset.unit,
                sell_price: parseFloat(card.dataset.price),
                stock:      parseInt(card.dataset.stock),
                image:      card.dataset.image,
                attrs:      card.dataset.attrs,
            }));
        });
    }

    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(searchProducts, 350);
    });
    catFilter?.addEventListener('change', searchProducts);

    // ─── Giỏ hàng ─────────────────────────────────────────────────────
    function addToCart(product) {
        // Dùng variant_id để identify item (schema v2.0)
        const existing = cart.find(i => i.variant_id === product.variant_id);
        if (existing) {
            if (existing.quantity >= product.stock) {
                alert(`Chỉ còn ${product.stock} ${product.unit} trong kho.`);
                return;
            }
            existing.quantity++;
        } else {
            cart.push({ ...product, quantity: 1 });
        }
        renderCart();
    }

    function renderCart() {
        if (!cartList) return;
        const items = cart.filter(i => i.quantity > 0);
        if (!items.length) {
            cartList.innerHTML = '<div class="text-center subtext py-4" id="cartEmpty">Chưa có sản phẩm.</div>';
            checkoutBtn && (checkoutBtn.disabled = true);
            updateTotals();
            return;
        }
        checkoutBtn && (checkoutBtn.disabled = false);
        cartList.innerHTML = items.map((item, idx) => {
            const imgThumb = item.image 
                ? `<img src="${cfg.baseUrl}/${item.image}" style="width:36px;height:36px;object-fit:cover;border-radius:var(--radius-sm);border:1px solid var(--border)">`
                : `<div style="width:36px;height:36px;background:var(--surface-hover);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:var(--text-muted);border:1px solid var(--border)"><i class="bi bi-box"></i></div>`;
            return `
            <div class="cart-item">
                ${imgThumb}
                <div style="flex:1;min-width:0;margin-left:8px">
                    <div class="fw-500" style="font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escHtml(item.name)}</div>
                    <div class="subtext" style="font-size:.75rem">${escHtml(item.sku)}${item.attrs ? ' • '+escHtml(item.attrs) : ''} • ${fmtMoney(item.sell_price)} / ${escHtml(item.unit)}</div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button class="qty-btn" onclick="POS.changeQty(${idx},-1)">−</button>
                    <span style="min-width:24px;text-align:center;font-size:.9rem">${item.quantity}</span>
                    <button class="qty-btn" onclick="POS.changeQty(${idx},1)">+</button>
                </div>
                <div style="min-width:75px;text-align:right;font-size:.85rem;font-weight:600">
                    ${fmtMoney(item.sell_price * item.quantity)}
                </div>
                <button class="btn btn-ghost btn-sm text-danger p-1" onclick="POS.removeItem(${idx})">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            `
        }).join('');
        updateTotals();
    }

    function updateTotals() {
        const subtotal = cart.reduce((s, i) => s + i.sell_price * i.quantity, 0);
        const discount = parseFloat(discountInput?.value || 0);
        const final    = Math.max(0, subtotal - discount);
        if (subtotalEl) subtotalEl.textContent = fmtMoney(subtotal);
        if (finalEl)    finalEl.textContent    = fmtMoney(final);
    }

    discountInput?.addEventListener('input', updateTotals);
    clearCartBtn?.addEventListener('click', () => { cart = []; renderCart(); });

    // ─── Khách hàng ───────────────────────────────────────────────────
    findCustBtn?.addEventListener('click', async () => {
        const phone = customerPhone?.value.trim();
        if (!phone) return;
        const res  = await fetch(`${cfg.findCustomerUrl}?phone=${encodeURIComponent(phone)}`);
        const data = await res.json();
        if (data) {
            customer = data;
            if (custNameEl)  custNameEl.textContent  = data.full_name;
            if (custPtsEl)   custPtsEl.textContent   = data.loyalty_points;
            if (custInfo)    custInfo.style.display  = '';
        } else {
            alert('Không tìm thấy khách hàng với SĐT này.\nBạn có thể thêm mới tại trang Khách hàng.');
        }
    });

    removeCustBtn?.addEventListener('click', () => {
        customer = null;
        if (custInfo)    custInfo.style.display  = 'none';
        if (customerPhone) customerPhone.value   = '';
    });

    // ─── Elements VietQR ─────────────────────────────────────────────
    const vietqrModalEl = document.getElementById('vietqrModal');
    const qrAmountDisplay = document.getElementById('qrAmountDisplay');
    const vietqrImage = document.getElementById('vietqrImage');
    const qrBankDisplay = document.getElementById('qrBankDisplay');
    const qrAccDisplay = document.getElementById('qrAccDisplay');
    const qrNameDisplay = document.getElementById('qrNameDisplay');
    const qrNoteDisplay = document.getElementById('qrNoteDisplay');
    const qrStatusText = document.getElementById('qrStatusText');
    const qrTimer = document.getElementById('qrTimer');
    const qrExpiredOverlay = document.getElementById('qrExpiredOverlay');
    const btnRegenQR = document.getElementById('btnRegenQR');
    const btnCancelQR = document.getElementById('btnCancelQR');
    const btnSimulateSuccess = document.getElementById('btnSimulateSuccess');
    const btnVerifyQR = document.getElementById('btnVerifyQR');
    const closeVietQRBtn = document.getElementById('closeVietQRBtn');

    async function checkRealPayment(orderId, amount) {
        if (!cfg.checkPaymentUrl) return;
        try {
            const checkRes = await fetch(cfg.checkPaymentUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId, amount: amount })
            });
            const checkData = await checkRes.json();
            if (checkData.ok && checkData.paid) {
                clearQRIntervals();
                if (qrStatusText) {
                    qrStatusText.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Đã nhận được tiền thanh toán thực tế!</span>';
                }
                setTimeout(() => {
                    const activeModal = bootstrap.Modal.getInstance(vietqrModalEl);
                    if (activeModal) activeModal.hide();
                    showSuccessModal(activeOrderId);
                }, 1500);
            }
        } catch (e) {
            console.error('Check payment error:', e);
        }
    }

    function clearQRIntervals() {
        if (qrTimerInterval) {
            clearInterval(qrTimerInterval);
            qrTimerInterval = null;
        }
        if (mockSuccessTimeout) {
            clearTimeout(mockSuccessTimeout);
            mockSuccessTimeout = null;
        }
        if (checkInterval) {
            clearInterval(checkInterval);
            checkInterval = null;
        }
    }

    function showSuccessModal(orderId) {
        if (successOrderEl) successOrderEl.textContent = '#' + orderId;
        if (receiptLink)    receiptLink.href = cfg.receiptBase + orderId;

        // Điểm tích lũy
        if (customer && loyaltyMsgEl) {
            const subtotal = cart.reduce((s, i) => s + i.sell_price * i.quantity, 0);
            const discount = parseFloat(discountInput?.value || 0);
            const points   = Math.floor(Math.max(0, subtotal - discount) / 1000);
            if (points > 0) {
                loyaltyMsgEl.textContent = `Khách hàng ${customer.full_name} được cộng +${points} điểm tích lũy.`;
                loyaltyMsgEl.style.display = '';
            }
        } else if (loyaltyMsgEl) {
            loyaltyMsgEl.style.display = 'none';
        }

        new bootstrap.Modal(document.getElementById('successModal')).show();
    }

    // ─── Thanh toán ───────────────────────────────────────────────────
    checkoutBtn?.addEventListener('click', async () => {
        if (!cart.length) return;
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';

        const payMethod = document.querySelector('input[name="payMethod"]:checked')?.value || 'cash';
        const subtotal = cart.reduce((s, i) => s + i.sell_price * i.quantity, 0);
        const discount = parseFloat(discountInput?.value || 0);
        const finalAmount = Math.max(0, subtotal - discount);

        const body = {
            customer_id:    customer?.customer_id || 0,
            discount:       discount,
            payment_method: payMethod,
            items: cart.map(i => ({ variant_id: i.variant_id, quantity: i.quantity })),
        };

        try {
            const res  = await fetch(cfg.storeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await res.json();

            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Thanh toán';

            if (data.ok) {
                if (payMethod === 'transfer') {
                    activeOrderId = data.order_id;
                    
                    // Cập nhật thông tin QR
                    if (qrAmountDisplay) qrAmountDisplay.textContent = fmtMoney(finalAmount);
                    if (qrBankDisplay) qrBankDisplay.textContent = cfg.bankId;
                    if (qrAccDisplay) qrAccDisplay.textContent = cfg.bankAccountNo;
                    if (qrNameDisplay) qrNameDisplay.textContent = cfg.bankAccountName;
                    
                    const qrNote = `HDBL${data.order_id}`;
                    if (qrNoteDisplay) qrNoteDisplay.textContent = qrNote;
                    
                    // Tạo link QR VietQR
                    let qrUrl = '';
                    const payLinkContainer = document.getElementById('payLinkContainer');
                    if (data.payos && data.payos.qrCode) {
                        // Dùng PayOS QR Code
                        qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(data.payos.qrCode)}`;
                        if (payLinkContainer) {
                            payLinkContainer.innerHTML = `<a href="${data.payos.checkoutUrl}" target="_blank" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-box-arrow-up-right me-1"></i> Mở trang thanh toán PayOS</a>`;
                            payLinkContainer.style.display = 'block';
                        }
                    } else {
                        // Fallback sang tự tạo link VietQR
                        qrUrl = `https://img.vietqr.io/image/${cfg.bankId}-${cfg.bankAccountNo}-compact2.png?amount=${finalAmount}&addInfo=${encodeURIComponent(qrNote)}&accountName=${encodeURIComponent(cfg.bankAccountName)}`;
                        if (payLinkContainer) {
                            payLinkContainer.style.display = 'none';
                            payLinkContainer.innerHTML = '';
                        }
                    }
                    if (vietqrImage) vietqrImage.src = qrUrl;
                    
                    // Hiển thị modal
                    const vqModal = new bootstrap.Modal(vietqrModalEl);
                    vqModal.show();
                    
                    // Khởi tạo trạng thái
                    if (qrExpiredOverlay) {
                        qrExpiredOverlay.classList.add('d-none');
                        qrExpiredOverlay.classList.remove('d-flex');
                    }
                    if (qrStatusText) {
                        qrStatusText.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-2"></span>Đang chờ khách quét mã...';
                    }
                    
                    // Đếm ngược 5 phút
                    let timeLeft = 300;
                    if (qrTimer) qrTimer.textContent = '05:00';
                    
                    clearQRIntervals();
                    
                    qrTimerInterval = setInterval(() => {
                        timeLeft--;
                        const m = Math.floor(timeLeft / 60);
                        const s = timeLeft % 60;
                        if (qrTimer) qrTimer.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                        
                        if (timeLeft <= 0) {
                            clearQRIntervals();
                            if (qrExpiredOverlay) {
                                qrExpiredOverlay.classList.remove('d-none');
                                qrExpiredOverlay.classList.add('d-flex');
                            }
                            if (qrStatusText) qrStatusText.textContent = 'Mã thanh toán đã hết hạn!';
                        }
                    }, 1000);
                    
                    // Kiểm tra giao dịch thực tế mỗi 3 giây
                    checkInterval = setInterval(() => checkRealPayment(data.order_id, finalAmount), 3000);
                } else {
                    showSuccessModal(data.order_id);
                }
            } else {
                alert('Lỗi: ' + (data.message || 'Không xác định'));
            }
        } catch (e) {
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Thanh toán';
            console.error(e);
            alert('Lỗi kết nối máy chủ.');
        }
    });

    // ─── Event Listeners cho VietQR Modal ──────────────────────────────
    btnVerifyQR?.addEventListener('click', () => {
        const activeModal = bootstrap.Modal.getInstance(vietqrModalEl);
        if (activeModal) activeModal.hide();
        clearQRIntervals();
        showSuccessModal(activeOrderId);
    });

    btnSimulateSuccess?.addEventListener('click', () => {
        if (qrStatusText) {
            qrStatusText.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> [Giả lập] Đã nhận tiền thành công!</span>';
        }
        setTimeout(() => {
            const activeModal = bootstrap.Modal.getInstance(vietqrModalEl);
            if (activeModal) activeModal.hide();
            clearQRIntervals();
            showSuccessModal(activeOrderId);
        }, 1000);
    });

    async function cancelActiveOrder() {
        if (!activeOrderId) return;
        if (!confirm('Bạn có chắc chắn muốn hủy giao dịch online và hủy đơn hàng này không?')) return;

        try {
            const res = await fetch(cfg.cancelOrderUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: activeOrderId })
            });
            const data = await res.json();
            if (data.ok) {
                const activeModal = bootstrap.Modal.getInstance(vietqrModalEl);
                if (activeModal) activeModal.hide();
                clearQRIntervals();
                activeOrderId = null;
                alert('Đơn hàng đã được hủy thành công.');
            } else {
                alert('Không thể hủy đơn hàng: ' + (data.message || 'Lỗi không xác định'));
            }
        } catch (e) {
            console.error('Cancel order error:', e);
            alert('Lỗi hệ thống khi hủy đơn.');
        }
    }

    btnCancelQR?.addEventListener('click', cancelActiveOrder);
    closeVietQRBtn?.addEventListener('click', cancelActiveOrder);
    
    btnRegenQR?.addEventListener('click', () => {
        // Tạo lại QR code bằng cách reset time
        if (qrExpiredOverlay) {
            qrExpiredOverlay.classList.add('d-none');
            qrExpiredOverlay.classList.remove('d-flex');
        }
        if (qrStatusText) {
            qrStatusText.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-2"></span>Đang chờ khách quét mã...';
        }
        
        let timeLeft = 300;
        if (qrTimer) qrTimer.textContent = '05:00';
        
        clearQRIntervals();
        
        qrTimerInterval = setInterval(() => {
            timeLeft--;
            const m = Math.floor(timeLeft / 60);
            const s = timeLeft % 60;
            if (qrTimer) qrTimer.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearQRIntervals();
                if (qrExpiredOverlay) {
                    qrExpiredOverlay.classList.remove('d-none');
                    qrExpiredOverlay.classList.add('d-flex');
                }
                if (qrStatusText) qrStatusText.textContent = 'Mã thanh toán đã hết hạn!';
            }
        }, 1000);
        
        const finalAmount = cart.reduce((s, i) => s + i.sell_price * i.quantity, 0) - parseFloat(discountInput?.value || 0);
        checkInterval = setInterval(() => checkRealPayment(activeOrderId, finalAmount), 3000);
    });

    newOrderBtn?.addEventListener('click', () => {
        cart     = [];
        customer = null;
        if (discountInput)  discountInput.value  = 0;
        if (customerPhone)  customerPhone.value  = '';
        if (custInfo)       custInfo.style.display = 'none';
        if (searchInput)    searchInput.value    = '';
        if (catFilter)      catFilter.value      = '';
        renderCart();
        searchProducts(); // reload toàn bộ sản phẩm
        bootstrap.Modal.getInstance(document.getElementById('successModal'))?.hide();
    });

    // ─── Expose to inline onclick ─────────────────────────────────────
    window.POS = {
        changeQty(idx, delta) {
            const item = cart[idx];
            if (!item) return;
            item.quantity += delta;
            if (item.quantity > item.stock) { item.quantity = item.stock; alert('Không đủ tồn kho.'); }
            if (item.quantity <= 0) cart.splice(idx, 1);
            renderCart();
        },
        removeItem(idx) { cart.splice(idx, 1); renderCart(); },
    };

    // ─── Helpers ─────────────────────────────────────────────────────
    function fmtMoney(n) {
        return Math.round(n).toLocaleString('vi-VN') + 'đ';
    }
    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Init — tự động load sản phẩm khi vào trang
    renderCart();
    searchProducts();
})();
