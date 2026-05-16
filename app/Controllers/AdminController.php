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
        $this->view('admin/dashboard', [
            'summary' => (new Inventory())->getSummary(),
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
}
