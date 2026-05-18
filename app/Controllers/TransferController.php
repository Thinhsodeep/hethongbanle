<?php

declare(strict_types=1);

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_ROOT . '/app/Middlewares/RoleMiddleware.php';
require_once APP_ROOT . '/app/Models/Transfer.php';
require_once APP_ROOT . '/app/Models/Inventory.php';
require_once APP_ROOT . '/app/Models/User.php';

final class TransferController extends Controller
{
    // Module 5 — Chuyển kho đa chi nhánh

    public function index(): void
    {
        RoleMiddleware::require('admin', 'manager', 'staff');
        $branchId  = RoleMiddleware::isAdmin() ? null : (int) $_SESSION['branch_id'];
        $pageTitle = 'Chuyển kho';
        $this->view('inventory/transfers/index', [
            'transfers' => (new Transfer())->getAll($branchId),
        ]);
    }

    public function create(): void
    {
        RoleMiddleware::require('admin', 'manager', 'staff');
        $transfer = new Transfer();
        $userModel = new User();
        $branches  = $userModel->getAllBranches();
        $myBranchId = RoleMiddleware::isAdmin() ? null : (int) $_SESSION['branch_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fromBranchId = (int) ($_POST['from_branch_id'] ?? 0);
            $toBranchId   = (int) ($_POST['to_branch_id']   ?? 0);

            // Validate
            if ($fromBranchId === $toBranchId || $fromBranchId === 0 || $toBranchId === 0) {
                $this->flash('danger', 'Chi nhánh xuất và nhận phải khác nhau.');
                $this->redirect('/transfer/create');
            }

            $variantIds = $_POST['variant_id']  ?? [];
            $quantities = $_POST['quantity']     ?? [];
            $items      = [];
            $inv        = new Inventory();
            $errors     = [];

            foreach ($variantIds as $k => $vid) {
                $vid = (int) $vid;
                $qty = (int) ($quantities[$k] ?? 0);
                if ($vid <= 0 || $qty <= 0) continue;

                $available = $transfer->getAvailableStock($fromBranchId, $vid);
                if ($qty > $available) {
                    $errors[] = "Variant #$vid: yêu cầu $qty nhưng chỉ còn $available.";
                    continue;
                }
                $items[] = ['variant_id' => $vid, 'quantity' => $qty];
            }

            if (!empty($errors)) {
                $this->flash('danger', implode('<br>', $errors));
                $this->redirect('/transfer/create');
            }

            if (empty($items)) {
                $this->flash('danger', 'Chưa có sản phẩm nào hợp lệ.');
                $this->redirect('/transfer/create');
            }

            $transferId = $transfer->create([
                'from_branch_id' => $fromBranchId,
                'to_branch_id'   => $toBranchId,
                'created_by'     => (int) $_SESSION['user_id'],
                'note'           => trim($_POST['note'] ?? ''),
            ], $items);

            $this->flash('success', 'Tạo phiếu chuyển kho #' . $transferId . ' thành công.');
            $this->redirect('/transfer/detail/' . $transferId);
        }

        $pageTitle = 'Tạo phiếu chuyển kho';
        $this->view('inventory/transfers/create', [
            'branches'    => $branches,
            'myBranchId'  => $myBranchId,
        ]);
    }

    public function detail(?string $id = null): void
    {
        RoleMiddleware::require('admin', 'manager', 'staff');
        $transfer = new Transfer();
        $data     = $transfer->findById((int) $id);
        if (!$data) {
            $this->flash('danger', 'Không tìm thấy phiếu chuyển kho.');
            $this->redirect('/transfer/index');
        }

        $pageTitle = 'Phiếu chuyển kho #' . $id;
        $this->view('inventory/transfers/view', [
            'transfer' => $data,
            'items'    => $transfer->getItems((int) $id),
        ]);
    }

    public function approve(?string $id = null): void
    {
        RoleMiddleware::require('admin', 'manager');
        $transfer = new Transfer();
        $data     = $transfer->findById((int) $id);
        if (!$data || $data['status'] !== 'pending') {
            $this->flash('danger', 'Không thể duyệt phiếu này.');
            $this->redirect('/transfer/index');
        }
        $transfer->updateStatus((int) $id, 'approved');
        $this->flash('success', 'Đã duyệt phiếu chuyển kho #' . $id . '.');
        $this->redirect('/transfer/detail/' . $id);
    }

    public function complete(?string $id = null): void
    {
        RoleMiddleware::require('admin', 'manager', 'staff');
        $transfer = new Transfer();
        $data     = $transfer->findById((int) $id);
        if (!$data || $data['status'] !== 'approved') {
            $this->flash('danger', 'Phiếu chưa được duyệt hoặc đã xử lý.');
            $this->redirect('/transfer/index');
        }

        $items = $transfer->getItems((int) $id);
        $inv   = new Inventory();

        // Validate lại tồn kho trước khi trừ
        foreach ($items as $item) {
            $available = $transfer->getAvailableStock($data['from_branch_id'], $item['variant_id']);
            if ($item['quantity'] > $available) {
                $this->flash('danger', 'Tồn kho không đủ để hoàn thành phiếu (SP: ' . htmlspecialchars($item['product_name']) . ').');
                $this->redirect('/transfer/detail/' . $id);
            }
        }

        // Áp dụng tồn kho
        foreach ($items as $item) {
            $inv->deductStock($data['from_branch_id'], $item['variant_id'], $item['quantity']);
            $inv->addStock($data['to_branch_id'],   $item['variant_id'], $item['quantity']);
        }

        $transfer->updateStatus((int) $id, 'completed');
        $this->flash('success', 'Phiếu #' . $id . ' hoàn thành — tồn kho đã được cập nhật.');
        $this->redirect('/transfer/detail/' . $id);
    }

    public function cancel(?string $id = null): void
    {
        RoleMiddleware::require('admin', 'manager');
        $transfer = new Transfer();
        $data     = $transfer->findById((int) $id);
        if (!$data || $data['status'] === 'completed') {
            $this->flash('danger', 'Không thể hủy phiếu đã hoàn thành.');
            $this->redirect('/transfer/index');
        }
        $transfer->updateStatus((int) $id, 'cancelled');
        $this->flash('success', 'Đã hủy phiếu chuyển kho #' . $id . '.');
        $this->redirect('/transfer/index');
    }

    /** AJAX: lấy danh sách SP có tồn kho tại chi nhánh */
    public function products(): void
    {
        RoleMiddleware::require('admin', 'manager', 'staff');
        $branchId = (int) ($_GET['branch_id'] ?? 0);
        header('Content-Type: application/json');
        if ($branchId === 0) {
            echo json_encode([]);
            exit;
        }
        echo json_encode((new Transfer())->getProductsInBranch($branchId));
        exit;
    }
}
