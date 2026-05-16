<?php

declare(strict_types=1);

require_once APP_ROOT . '/app/Middlewares/RoleMiddleware.php';
require_once APP_ROOT . '/app/Models/Inventory.php';
require_once APP_ROOT . '/app/Models/User.php';

class InventoryController extends Controller
{
    public function index(): void
    {
        RoleMiddleware::require('admin', 'manager', 'staff');
        $branchId = RoleMiddleware::isAdmin()
            ? ((int) ($_GET['branch_id'] ?? 0) ?: null)
            : (int) $_SESSION['branch_id'];

        $pageTitle = 'Tồn kho';
        $this->view('inventory/index', [
            'stocks'         => (new Inventory())->getByBranch($branchId),
            'branches'       => (new User())->getAllBranches(),
            'selectedBranch' => $branchId,
        ]);
    }

    public function alerts(): void
    {
        RoleMiddleware::require('admin', 'manager', 'staff');
        $branchId  = RoleMiddleware::isAdmin() ? null : (int) $_SESSION['branch_id'];
        $inventory = new Inventory();
        $pageTitle = 'Cảnh báo tồn kho';
        $this->view('inventory/alerts', [
            'lowStock'   => $inventory->getByBranch($branchId, 'Sắp hết'),
            'outOfStock' => $inventory->getByBranch($branchId, 'Hết hàng'),
        ]);
    }

    public function adjust(): void
    {
        RoleMiddleware::require('admin', 'manager');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new Inventory())->adjustQuantity(
                (int) $_POST['branch_id'],
                (int) $_POST['product_id'],
                (int) $_POST['quantity'],
                (int) $_POST['min_quantity']
            );
            $this->flash('success', 'Cập nhật tồn kho thành công.');
            $this->redirect('/inventory/index');
        }
    }

    public function exportCsv(): void
    {
        RoleMiddleware::require('admin', 'manager', 'staff');
        $branchId = RoleMiddleware::isAdmin()
            ? ((int) ($_GET['branch_id'] ?? 0) ?: null)
            : (int) $_SESSION['branch_id'];

        $rows = (new Inventory())->exportCsv($branchId);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=inventory_' . date('Y-m-d') . '.csv');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, ['Chi nhánh', 'SKU', 'Sản phẩm', 'Danh mục', 'SL', 'Tối thiểu', 'Trạng thái']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['branch_name'],
                $r['sku'],
                $r['product_name'],
                $r['category_name'],
                $r['quantity'],
                $r['min_quantity'],
                $r['stock_status'],
            ]);
        }
        fclose($out);
        exit;
    }
}
