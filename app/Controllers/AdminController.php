<?php

declare(strict_types=1);

require_once APP_ROOT . '/app/Middlewares/RoleMiddleware.php';
require_once APP_ROOT . '/app/Models/User.php';
require_once APP_ROOT . '/app/Models/Inventory.php';

class AdminController extends Controller
{
    public function dashboard(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $pageTitle = 'Dashboard';
        require_once APP_ROOT . '/app/Models/Order.php';
        $this->view('admin/dashboard', [
            'summary' => (new Inventory())->getSummary(),
            'revenue' => (new Order())->getRevenueLast7Days(),
        ]);
    }

    public function branches(): void
    {
        RoleMiddleware::require('admin');
        $pageTitle = 'Chi nhánh';
        $this->view('admin/branches', [
            'branches' => (new User())->getAllBranches('all'),
        ]);
    }

    public function createBranch(): void
    {
        RoleMiddleware::require('admin');
        $pageTitle = 'Thêm chi nhánh';
        $userModel = new User();
        $this->view('admin/branch_form', [
            'branch'   => null,
            'managers' => $userModel->getManagersForSelect(),
        ]);
    }

    public function storeBranch(): void
    {
        RoleMiddleware::require('admin');
        (new User())->createBranch($_POST);
        $this->flash('success', 'Thêm chi nhánh thành công.');
        $this->redirect('/admin/branches');
    }

    public function editBranch(?string $id): void
    {
        RoleMiddleware::require('admin');
        $userModel = new User();
        $branch = $userModel->findBranchById((int) $id);
        if (!$branch) {
            $this->redirect('/admin/branches');
        }
        $pageTitle = 'Sửa chi nhánh';
        $this->view('admin/branch_form', [
            'branch'   => $branch,
            'managers' => $userModel->getManagersForSelect(),
        ]);
    }

    public function updateBranch(?string $id): void
    {
        RoleMiddleware::require('admin');
        (new User())->updateBranch((int) $id, $_POST);
        $this->flash('success', 'Cập nhật chi nhánh thành công.');
        $this->redirect('/admin/branches');
    }

    public function users(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $branchId = RoleMiddleware::isManager() ? (int) $_SESSION['branch_id'] : null;
        $pageTitle = 'Nhân viên';
        $this->view('admin/users', [
            'users' => (new User())->getAll($branchId),
        ]);
    }

    public function createUser(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $userModel = new User();
        $branches = $userModel->getAllBranches();
        if (RoleMiddleware::isManager()) {
            $branches = array_filter($branches, fn ($b) => (int) $b['branch_id'] === (int) $_SESSION['branch_id']);
        }
        $pageTitle = 'Thêm nhân viên';
        $this->view('admin/user_form', [
            'user'     => null,
            'branches' => $branches,
            'roles'    => $userModel->getAllRoles(),
        ]);
    }

    public function storeUser(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $data = $_POST;
        if (RoleMiddleware::isManager()) {
            $data['branch_id'] = $_SESSION['branch_id'];
        }
        (new User())->create($data);
        $this->flash('success', 'Thêm nhân viên thành công.');
        $this->redirect('/admin/users');
    }

    public function editUser(?string $id): void
    {
        RoleMiddleware::require('admin', 'manager');
        $userModel = new User();
        $user = $userModel->findById((int) $id);
        if (!$user) {
            $this->redirect('/admin/users');
        }
        if (RoleMiddleware::isManager() && (int) $user['branch_id'] !== (int) $_SESSION['branch_id']) {
            RoleMiddleware::require('admin');
        }
        $branches = $userModel->getAllBranches();
        if (RoleMiddleware::isManager()) {
            $branches = array_filter($branches, fn ($b) => (int) $b['branch_id'] === (int) $_SESSION['branch_id']);
        }
        $pageTitle = 'Sửa nhân viên';
        $this->view('admin/user_form', [
            'user'     => $user,
            'branches' => $branches,
            'roles'    => $userModel->getAllRoles(),
        ]);
    }

    public function updateUser(?string $id): void
    {
        RoleMiddleware::require('admin', 'manager');
        $data = $_POST;
        if (RoleMiddleware::isManager()) {
            $data['branch_id'] = $_SESSION['branch_id'];
        }
        (new User())->update((int) $id, $data);
        $this->flash('success', 'Cập nhật nhân viên thành công.');
        $this->redirect('/admin/users');
    }

    public function updatePaymentSettings(): void
    {
        RoleMiddleware::require('admin');

        $bankId = trim($_POST['bank_id'] ?? 'MB');
        $bankAccount = trim($_POST['bank_account_no'] ?? '');
        $bankName = trim($_POST['bank_account_name'] ?? '');
        $payosClientId = trim($_POST['payos_client_id'] ?? '');
        $payosApiKey = trim($_POST['payos_api_key'] ?? '');
        $payosChecksumKey = trim($_POST['payos_checksum_key'] ?? '');

        $envPath = ROOT_PATH . '/.env';
        if (is_file($envPath) && is_writable($envPath)) {
            $content = file_get_contents($envPath);

            // Replace BANK_ID
            if (preg_match('/^BANK_ID=.*$/m', $content)) {
                $content = preg_replace('/^BANK_ID=.*$/m', 'BANK_ID=' . $bankId, $content);
            } else {
                $content .= "\nBANK_ID=" . $bankId;
            }

            // Replace BANK_ACCOUNT_NO
            if (preg_match('/^BANK_ACCOUNT_NO=.*$/m', $content)) {
                $content = preg_replace('/^BANK_ACCOUNT_NO=.*$/m', 'BANK_ACCOUNT_NO=' . $bankAccount, $content);
            } else {
                $content .= "\nBANK_ACCOUNT_NO=" . $bankAccount;
            }

            // Replace BANK_ACCOUNT_NAME
            if (preg_match('/^BANK_ACCOUNT_NAME=.*$/m', $content)) {
                $content = preg_replace('/^BANK_ACCOUNT_NAME=.*$/m', 'BANK_ACCOUNT_NAME=' . $bankName, $content);
            } else {
                $content .= "\nBANK_ACCOUNT_NAME=" . $bankName;
            }

            // Replace PAYOS_CLIENT_ID
            if (preg_match('/^PAYOS_CLIENT_ID=.*$/m', $content)) {
                $content = preg_replace('/^PAYOS_CLIENT_ID=.*$/m', 'PAYOS_CLIENT_ID=' . $payosClientId, $content);
            } else {
                $content .= "\nPAYOS_CLIENT_ID=" . $payosClientId;
            }

            // Replace PAYOS_API_KEY
            if (preg_match('/^PAYOS_API_KEY=.*$/m', $content)) {
                $content = preg_replace('/^PAYOS_API_KEY=.*$/m', 'PAYOS_API_KEY=' . $payosApiKey, $content);
            } else {
                $content .= "\nPAYOS_API_KEY=" . $payosApiKey;
            }

            // Replace PAYOS_CHECKSUM_KEY
            if (preg_match('/^PAYOS_CHECKSUM_KEY=.*$/m', $content)) {
                $content = preg_replace('/^PAYOS_CHECKSUM_KEY=.*$/m', 'PAYOS_CHECKSUM_KEY=' . $payosChecksumKey, $content);
            } else {
                $content .= "\nPAYOS_CHECKSUM_KEY=" . $payosChecksumKey;
            }

            // Clean up old CASSO_API_KEY if exists
            $content = preg_replace('/^CASSO_API_KEY=.*$\n?/m', '', $content);

            file_put_contents($envPath, trim($content) . "\n");
            $this->flash('success', 'Cập nhật cấu hình thanh toán online thành công.');
        } else {
            $this->flash('danger', 'Không thể ghi file .env. Vui lòng kiểm tra quyền ghi file.');
        }

        $this->redirect('/admin/dashboard');
    }
}

