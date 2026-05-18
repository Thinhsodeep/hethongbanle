<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn #<?= (int) $order['order_id'] ?> — Retail Chain</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Courier New', monospace; font-size:12px; max-width:300px; margin:0 auto; padding:10px; }
        .center { text-align:center; }
        .bold { font-weight:bold; }
        .divider { border-top:1px dashed #000; margin:6px 0; }
        table { width:100%; border-collapse:collapse; }
        td { vertical-align:top; }
        td:last-child { text-align:right; white-space:nowrap; }
        .total-row td { font-weight:bold; border-top:1px solid #000; padding-top:4px; }
        .footer { text-align:center; margin-top:8px; font-size:10px; }
        @media print {
            body { max-width:none; }
            .no-print { display:none !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align:center;margin-bottom:12px">
    <button onclick="window.print()" style="padding:6px 16px;cursor:pointer">In hóa đơn</button>
    <a href="javascript:window.close()" style="margin-left:8px">Đóng</a>
</div>

<div class="center bold" style="font-size:14px">RETAIL CHAIN</div>
<div class="center"><?= htmlspecialchars($order['branch_name']) ?></div>
<div class="divider"></div>

<div class="center bold">HÓA ĐƠN BÁN HÀNG</div>
<div class="center">Số: #<?= (int) $order['order_id'] ?></div>
<div class="divider"></div>

<table>
    <tr><td>Ngày:</td><td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td></tr>
    <tr><td>Thu ngân:</td><td><?= htmlspecialchars($order['cashier_name']) ?></td></tr>
    <?php if ($order['customer_name']): ?>
    <tr><td>Khách hàng:</td><td><?= htmlspecialchars($order['customer_name']) ?></td></tr>
    <?php if ($order['customer_phone']): ?>
    <tr><td>SĐT:</td><td><?= htmlspecialchars($order['customer_phone']) ?></td></tr>
    <?php endif; ?>
    <?php else: ?>
    <tr><td>Khách hàng:</td><td>Khách vãng lai</td></tr>
    <?php endif; ?>
</table>

<div class="divider"></div>

<table>
    <tr>
        <td class="bold">Sản phẩm</td>
        <td class="bold" style="text-align:right">T.Tiền</td>
    </tr>
</table>
<div class="divider"></div>
<table>
    <?php foreach ($items as $item):
        $sub = $item['quantity'] * $item['unit_price'];
    ?>
    <tr>
        <td style="padding-right:4px">
            <div><?= htmlspecialchars($item['product_name']) ?></div>
            <div style="color:#555"><?= $item['quantity'] ?> x <?= number_format((float)$item['unit_price'], 0, ',', '.') ?>đ</div>
        </td>
        <td><?= number_format($sub, 0, ',', '.') ?>đ</td>
    </tr>
    <?php endforeach; ?>
</table>

<div class="divider"></div>
<table>
    <tr>
        <td>Tổng tiền hàng:</td>
        <td><?= number_format((float)$order['total_amount'], 0, ',', '.') ?>đ</td>
    </tr>
    <?php if ($order['discount'] > 0): ?>
    <tr>
        <td>Giảm giá:</td>
        <td>-<?= number_format((float)$order['discount'], 0, ',', '.') ?>đ</td>
    </tr>
    <?php endif; ?>
    <tr class="total-row">
        <td>THỰC THU:</td>
        <td><?= number_format((float)$order['final_amount'], 0, ',', '.') ?>đ</td>
    </tr>
    <tr>
        <td>Thanh toán:</td>
        <td><?php
            $pm = ['cash'=>'Tiền mặt','card'=>'Thẻ','transfer'=>'CK'];
            echo $pm[$order['payment_method']] ?? $order['payment_method'];
        ?></td>
    </tr>
</table>

<div class="divider"></div>
<div class="footer">
    <div>Cảm ơn quý khách đã mua hàng!</div>
    <div>Hẹn gặp lại.</div>
</div>

</body>
</html>
