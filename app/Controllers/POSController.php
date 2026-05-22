<?php

declare(strict_types=1);

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_ROOT . '/app/Middlewares/RoleMiddleware.php';
require_once APP_ROOT . '/app/Models/Order.php';
require_once APP_ROOT . '/app/Models/Customer.php';
require_once APP_ROOT . '/app/Models/Inventory.php';
require_once APP_ROOT . '/app/Models/Product.php';

final class POSController extends Controller
{
    // Module 7 — Bán hàng / POS

    public function index(): void
    {
        RoleMiddleware::require('admin', 'manager', 'cashier');
        $pageTitle = 'Bán hàng (POS)';
        $this->view('pos/index', [
            'categories' => (new Product())->getAllCategories(),
            'branchId'   => (int) $_SESSION['branch_id'],
        ]);
    }

    /**
     * AJAX: tìm variant sản phẩm theo SKU / barcode / tên.
     * Trả về JSON mỗi phần tử = 1 variant có kèm tồn kho chi nhánh hiện tại.
     */
    public function search(): void
    {
        RoleMiddleware::require('admin', 'manager', 'cashier');
        $kw       = trim($_GET['q'] ?? '');
        $catId    = (int) ($_GET['cat'] ?? 0) ?: null;
        $branchId = (int) $_SESSION['branch_id'];

        header('Content-Type: application/json; charset=utf-8');

        $variants = (new Product())->search($kw, $catId);
        $inv      = new Inventory();
        $result   = [];
        foreach ($variants as $v) {
            $row = $inv->findInventoryRow($branchId, (int) $v['variant_id']);
            $v['stock'] = $row ? (int) $row['quantity'] : 0;
            // Alias cho JS dễ dùng
            $v['name']       = $v['product_name'];
            $v['sell_price'] = (float) $v['sell_price'];
            $result[] = $v;
        }
        echo json_encode($result);
        exit;
    }

    /** AJAX: tìm khách hàng theo SĐT */
    public function findCustomer(): void
    {
        RoleMiddleware::require('admin', 'manager', 'cashier');
        $phone = trim($_GET['phone'] ?? '');
        header('Content-Type: application/json');
        if ($phone === '') { echo json_encode(null); exit; }
        echo json_encode((new Customer())->findByPhone($phone) ?: null);
        exit;
    }

    /**
     * POST: tạo đơn bán hàng.
     * Body JSON: { customer_id, discount, payment_method, items: [{variant_id, quantity}] }
     */
    public function store(): void
    {
        RoleMiddleware::require('admin', 'manager', 'cashier');
        header('Content-Type: application/json');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $branchId   = (int) $_SESSION['branch_id'];
        $customerId = (int) ($body['customer_id'] ?? 0) ?: null;
        $discount   = (float) ($body['discount']  ?? 0);
        $payMethod  = in_array($body['payment_method'] ?? 'cash', ['cash','card','transfer'])
                      ? $body['payment_method']
                      : 'cash';
        $rawItems   = $body['items'] ?? [];

        if (empty($rawItems)) {
            echo json_encode(['ok' => false, 'message' => 'Giỏ hàng trống.']);
            exit;
        }

        $inv        = new Inventory();
        $product    = new Product();
        $orderItems = [];

        foreach ($rawItems as $item) {
            $variantId = (int) ($item['variant_id'] ?? 0);
            $qty       = (int) ($item['quantity']   ?? 0);
            if ($variantId <= 0 || $qty <= 0) continue;

            $variant = $product->findVariantById($variantId);
            if (!$variant) {
                echo json_encode(['ok' => false, 'message' => "Variant #$variantId không tồn tại."]);
                exit;
            }

            $invRow = $inv->findInventoryRow($branchId, $variantId);
            if (!$invRow || $invRow['quantity'] < $qty) {
                echo json_encode(['ok' => false, 'message' => "Tồn kho không đủ: {$variant['product_name']} ({$variant['sku']})."]);
                exit;
            }
            $orderItems[] = [
                'variant_id' => $variantId,
                'quantity'   => $qty,
                'unit_price' => (float) $variant['sell_price'],
            ];
        }

        if (empty($orderItems)) {
            echo json_encode(['ok' => false, 'message' => 'Không có sản phẩm hợp lệ.']);
            exit;
        }

        try {
            $order   = new Order();
            $orderId = $order->create([
                'branch_id'      => $branchId,
                'customer_id'    => $customerId,
                'created_by'     => (int) $_SESSION['user_id'],
                'discount'       => $discount,
                'payment_method' => $payMethod,
            ], $orderItems);

            // Trừ tồn kho theo variant_id
            foreach ($orderItems as $item) {
                $inv->deductStock($branchId, $item['variant_id'], $item['quantity']);
            }

            // Tích điểm: 1.000 VND = 1 điểm
            if ($customerId) {
                $orderData = $order->findById($orderId);
                $points    = (int) floor((float) $orderData['final_amount'] / 1000);
                if ($points > 0) {
                    (new Customer())->addLoyaltyPoints($customerId, $points);
                }
            }

            $payosData = null;

            if ($payMethod === 'transfer') {
                $payosClientId = defined('PAYOS_CLIENT_ID') ? PAYOS_CLIENT_ID : '';
                $payosApiKey = defined('PAYOS_API_KEY') ? PAYOS_API_KEY : '';
                $payosChecksumKey = defined('PAYOS_CHECKSUM_KEY') ? PAYOS_CHECKSUM_KEY : '';

                if (!empty($payosClientId) && !empty($payosApiKey) && !empty($payosChecksumKey)) {
                    $orderData = $order->findById($orderId);
                    $finalAmount = (int) $orderData['final_amount'];

                    // PayOS Payload
                    $payosPayload = [
                        'orderCode'   => $orderId,
                        'amount'      => $finalAmount,
                        'description' => 'HDBL' . $orderId,
                        'cancelUrl'   => BASE_URL . '/pos/index',
                        'returnUrl'   => BASE_URL . '/pos/index',
                    ];

                    // Sort alphabetical by key
                    ksort($payosPayload);

                    // Generate signature query string (raw unencoded values)
                    $queryString = "";
                    $count = 0;
                    foreach ($payosPayload as $key => $value) {
                        if ($count > 0) {
                            $queryString .= "&";
                        }
                        $queryString .= $key . "=" . $value;
                        $count++;
                    }

                    $signature   = hash_hmac('sha256', $queryString, $payosChecksumKey);
                    $payosPayload['signature'] = $signature;

                    // Make request to PayOS
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://api-merchant.payos.vn/v2/payment-requests');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'x-client-id: ' . $payosClientId,
                        'x-api-key: ' . $payosApiKey,
                        'Content-Type: application/json',
                    ]);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payosPayload));
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);
                    curl_close($ch);

                    file_put_contents(ROOT_PATH . '/payos_debug.log', date('Y-m-d H:i:s') . " - store (Order: $orderId):\nHTTP Code: $httpCode\nPayload: " . json_encode($payosPayload) . "\nResponse: $response\ncURL Error: $curlError\n\n", FILE_APPEND);

                    if ($httpCode === 200) {
                        $resData = json_decode($response, true);
                        if (($resData['error'] ?? 0) === 0 && isset($resData['data'])) {
                            $payosData = [
                                'checkoutUrl'   => $resData['data']['checkoutUrl'],
                                'qrCode'        => $resData['data']['qrCode'] ?? '',
                                'paymentLinkId' => $resData['data']['paymentLinkId'] ?? '',
                            ];
                        }
                    }
                }
            }

            echo json_encode([
                'ok'       => true,
                'order_id' => $orderId,
                'payos'    => $payosData,
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['ok' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
        exit;
    }

    public function history(): void
    {
        RoleMiddleware::require('admin', 'manager', 'cashier');
        $branchId  = RoleMiddleware::isAdmin() ? null : (int) $_SESSION['branch_id'];
        $date      = $_GET['date'] ?? date('Y-m-d');
        $pageTitle = 'Lịch sử đơn bán';
        $this->view('pos/history', [
            'orders'   => (new Order())->getByBranch($branchId, $date),
            'date'     => $date,
            'branchId' => $branchId,
        ]);
    }

    public function receipt(?string $id = null): void
    {
        RoleMiddleware::require('admin', 'manager', 'cashier');
        $order = new Order();
        $data  = $order->findById((int) $id);
        if (!$data) {
            $this->flash('danger', 'Không tìm thấy đơn hàng.');
            $this->redirect('/pos/history');
        }
        // Render raw (không dùng layout của hệ thống)
        $items = $order->getItems((int) $id);
        extract(['order' => $data, 'items' => $items]);
        require_once APP_ROOT . '/app/Views/pos/receipt.php';
        exit;
    }

    /**
     * POST: Hủy đơn hàng đang thanh toán dở dang.
     * Body JSON: { order_id }
     */
    public function cancelOrder(): void
    {
        RoleMiddleware::require('admin', 'manager', 'cashier');
        header('Content-Type: application/json');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $orderId = (int) ($body['order_id'] ?? 0);
        $branchId = (int) $_SESSION['branch_id'];

        if ($orderId <= 0) {
            echo json_encode(['ok' => false, 'message' => 'Mã đơn hàng không hợp lệ.']);
            exit;
        }

        $orderModel = new Order();
        $order = $orderModel->findById($orderId);
        if (!$order) {
            echo json_encode(['ok' => false, 'message' => 'Không tìm thấy đơn hàng.']);
            exit;
        }

        $items = $orderModel->getItems($orderId);
        $ok = $orderModel->cancel($orderId, $branchId, $items);

        echo json_encode(['ok' => $ok]);
        exit;
    }

    /**
     * POST: Kiểm tra giao dịch thực tế qua API PayOS.
     * Body JSON: { order_id, amount }
     */
    public function checkPayment(): void
    {
        RoleMiddleware::require('admin', 'manager', 'cashier');
        header('Content-Type: application/json');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $orderId = (int) ($body['order_id'] ?? 0);
        $amount = (float) ($body['amount'] ?? 0);

        if ($orderId <= 0) {
            echo json_encode(['ok' => false, 'message' => 'Mã đơn hàng không hợp lệ.']);
            exit;
        }

        $payosClientId = defined('PAYOS_CLIENT_ID') ? PAYOS_CLIENT_ID : '';
        $payosApiKey = defined('PAYOS_API_KEY') ? PAYOS_API_KEY : '';

        if (empty($payosClientId) || empty($payosApiKey)) {
            // Không cấu hình key thực tế, trả về false để JS xử lý giả lập hoặc chờ click tay
            echo json_encode([
                'ok' => true,
                'paid' => false,
                'message' => 'PayOS chưa được cấu hình. Sử dụng chế độ xác nhận tay hoặc giả lập.'
            ]);
            exit;
        }

        // Gọi API PayOS để lấy thông tin đơn hàng
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api-merchant.payos.vn/v2/payment-requests/' . $orderId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-client-id: ' . $payosClientId,
            'x-api-key: ' . $payosApiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        file_put_contents(ROOT_PATH . '/payos_debug.log', date('Y-m-d H:i:s') . " - checkPayment (Order: $orderId, Amount: $amount):\nHTTP Code: $httpCode\nResponse: $response\ncURL Error: $curlError\n\n", FILE_APPEND);

        if ($httpCode !== 200) {
            echo json_encode([
                'ok' => true,
                'paid' => false,
                'message' => 'Lỗi kết nối PayOS API hoặc đơn hàng chưa thanh toán.'
            ]);
            exit;
        }

        $data = json_decode($response, true);
        if (($data['error'] ?? 0) === 0 && isset($data['data'])) {
            $status = $data['data']['status'] ?? '';
            $paid = ($status === 'PAID');
            echo json_encode([
                'ok' => true,
                'paid' => $paid,
                'status' => $status
            ]);
            exit;
        }

        echo json_encode([
            'ok' => true,
            'paid' => false
        ]);
        exit;
    }
}

