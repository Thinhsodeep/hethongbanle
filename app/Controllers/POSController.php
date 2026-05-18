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

            echo json_encode(['ok' => true, 'order_id' => $orderId]);
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
}
