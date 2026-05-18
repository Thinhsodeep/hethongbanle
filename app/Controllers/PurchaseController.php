<?php

declare(strict_types=1);

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_ROOT . '/app/Middlewares/RoleMiddleware.php';
require_once APP_ROOT . '/app/Models/Purchase.php';
require_once APP_ROOT . '/app/Models/Supplier.php';
require_once APP_ROOT . '/app/Models/Inventory.php';
require_once APP_ROOT . '/app/Models/Product.php';
require_once APP_ROOT . '/app/Models/User.php';

final class PurchaseController extends Controller
{
    // Module 6 — Nhập hàng từ NCC

    public function index(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $branchId  = RoleMiddleware::isAdmin() ? null : (int) $_SESSION['branch_id'];
        $pageTitle = 'Đơn nhập hàng';
        $this->view('purchases/index', [
            'orders' => (new Purchase())->getAll($branchId),
        ]);
    }

    public function create(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $purchase  = new Purchase();
        $userModel = new User();
        $myBranchId = RoleMiddleware::isAdmin() ? null : (int) $_SESSION['branch_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $branchId   = (int) ($_POST['branch_id']   ?? 0);
            $supplierId = (int) ($_POST['supplier_id'] ?? 0);

            if ($branchId === 0 || $supplierId === 0) {
                $this->flash('danger', 'Vui lòng chọn chi nhánh và nhà cung cấp.');
                $this->redirect('/purchase/create');
            }

            $variantIds = $_POST['variant_id'] ?? [];
            $quantities = $_POST['quantity']   ?? [];
            $unitPrices = $_POST['unit_price'] ?? [];
            $items      = [];

            foreach ($variantIds as $k => $vid) {
                $vid   = (int) $vid;
                $qty   = (int) ($quantities[$k] ?? 0);
                $price = (float) ($unitPrices[$k] ?? 0);
                if ($vid <= 0 || $qty <= 0 || $price <= 0) continue;
                $items[] = ['variant_id' => $vid, 'quantity' => $qty, 'unit_price' => $price];
            }

            if (empty($items)) {
                $this->flash('danger', 'Chưa có sản phẩm nào hợp lệ.');
                $this->redirect('/purchase/create');
            }

            $poId = $purchase->create([
                'branch_id'   => $branchId,
                'supplier_id' => $supplierId,
                'created_by'  => (int) $_SESSION['user_id'],
                'note'        => trim($_POST['note'] ?? ''),
            ], $items);

            $this->flash('success', 'Tạo đơn nhập hàng #' . $poId . ' thành công.');
            $this->redirect('/purchase/detail/' . $poId);
        }

        $pageTitle = 'Tạo đơn nhập hàng';
        $this->view('purchases/create', [
            'branches'   => $userModel->getAllBranches(),
            'suppliers'  => (new Supplier())->getAll('active'),
            'variants'   => (new Product())->search(),   // 1 dòng = 1 variant (v2.0)
            'myBranchId' => $myBranchId,
        ]);
    }

    public function detail(?string $id = null): void
    {
        RoleMiddleware::require('admin', 'manager');
        $purchase = new Purchase();
        $data     = $purchase->findById((int) $id);
        if (!$data) {
            $this->flash('danger', 'Không tìm thấy đơn nhập hàng.');
            $this->redirect('/purchase/index');
        }
        $pageTitle = 'Đơn nhập hàng #' . $id;
        $this->view('purchases/view', [
            'order' => $data,
            'items' => $purchase->getItems((int) $id),
        ]);
    }

    public function receive(?string $id = null): void
    {
        RoleMiddleware::require('admin', 'manager');
        $purchase = new Purchase();
        $data     = $purchase->findById((int) $id);
        if (!$data || $data['status'] !== 'pending') {
            $this->flash('danger', 'Đơn hàng không ở trạng thái có thể nhận.');
            $this->redirect('/purchase/index');
        }

        $items = $purchase->getItems((int) $id);
        $inv   = new Inventory();
        foreach ($items as $item) {
            // schema v2.0: dùng variant_id
            $inv->addStock($data['branch_id'], $item['variant_id'], $item['quantity']);
        }

        $purchase->updateStatus((int) $id, 'received');
        $this->flash('success', 'Đã nhận hàng — tồn kho chi nhánh đã được cập nhật.');
        $this->redirect('/purchase/detail/' . $id);
    }

    public function cancel(?string $id = null): void
    {
        RoleMiddleware::require('admin', 'manager');
        $purchase = new Purchase();
        $data     = $purchase->findById((int) $id);
        if (!$data || $data['status'] !== 'pending') {
            $this->flash('danger', 'Chỉ hủy được đơn hàng đang chờ.');
            $this->redirect('/purchase/index');
        }
        $purchase->updateStatus((int) $id, 'cancelled');
        $this->flash('success', 'Đã hủy đơn nhập hàng #' . $id . '.');
        $this->redirect('/purchase/index');
    }

    // ─── Nhà cung cấp ────────────────────────────────────────────

    public function suppliers(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $pageTitle = 'Nhà cung cấp';
        $this->view('purchases/suppliers/index', [
            'suppliers' => (new Supplier())->getAll(),
        ]);
    }

    public function supplierSave(): void
    {
        RoleMiddleware::require('admin', 'manager');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/purchase/suppliers');
        }

        $supplier = new Supplier();
        $id       = (int) ($_POST['supplier_id'] ?? 0);
        $data     = [
            'name'    => trim($_POST['name']    ?? ''),
            'phone'   => trim($_POST['phone']   ?? ''),
            'email'   => trim($_POST['email']   ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'status'  => $_POST['status'] ?? 'active',
        ];

        if ($data['name'] === '') {
            $this->flash('danger', 'Tên nhà cung cấp không được để trống.');
            $this->redirect('/purchase/suppliers');
        }

        if ($id > 0) {
            $supplier->update($id, $data);
            $this->flash('success', 'Đã cập nhật nhà cung cấp.');
        } else {
            $supplier->create($data);
            $this->flash('success', 'Đã thêm nhà cung cấp mới.');
        }
        $this->redirect('/purchase/suppliers');
    }
}
