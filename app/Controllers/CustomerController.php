<?php

declare(strict_types=1);

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_ROOT . '/app/Middlewares/RoleMiddleware.php';
require_once APP_ROOT . '/app/Models/Customer.php';

final class CustomerController extends Controller
{
    // Module 8 — Quản lý khách hàng

    public function index(): void
    {
        RoleMiddleware::require('admin', 'manager', 'cashier');
        $search    = trim($_GET['q'] ?? '');
        $pageTitle = 'Khách hàng';
        $this->view('customers/index', [
            'customers' => (new Customer())->getAll($search ?: null),
            'search'    => $search,
        ]);
    }

    public function create(): void
    {
        RoleMiddleware::require('admin', 'manager', 'cashier');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => trim($_POST['full_name'] ?? ''),
                'phone'     => trim($_POST['phone']     ?? '') ?: null,
                'email'     => trim($_POST['email']     ?? '') ?: null,
                'address'   => trim($_POST['address']   ?? '') ?: null,
            ];
            if ($data['full_name'] === '') {
                $this->flash('danger', 'Tên khách hàng không được để trống.');
                $this->redirect('/customer/create');
            }
            (new Customer())->create($data);
            $this->flash('success', 'Đã thêm khách hàng mới.');
            $this->redirect('/customer/index');
        }
        $pageTitle = 'Thêm khách hàng';
        $this->view('customers/form', ['customer' => null]);
    }

    public function edit(?string $id = null): void
    {
        RoleMiddleware::require('admin', 'manager', 'cashier');
        $customer = new Customer();
        $data     = $customer->findById((int) $id);
        if (!$data) {
            $this->flash('danger', 'Không tìm thấy khách hàng.');
            $this->redirect('/customer/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'full_name' => trim($_POST['full_name'] ?? ''),
                'phone'     => trim($_POST['phone']     ?? '') ?: null,
                'email'     => trim($_POST['email']     ?? '') ?: null,
                'address'   => trim($_POST['address']   ?? '') ?: null,
            ];
            if ($d['full_name'] === '') {
                $this->flash('danger', 'Tên khách hàng không được để trống.');
                $this->redirect('/customer/edit/' . $id);
            }
            $customer->update((int) $id, $d);
            $this->flash('success', 'Đã cập nhật thông tin khách hàng.');
            $this->redirect('/customer/index');
        }

        $pageTitle = 'Sửa khách hàng';
        $this->view('customers/form', ['customer' => $data]);
    }

    public function delete(?string $id = null): void
    {
        RoleMiddleware::require('admin', 'manager');
        $result = (new Customer())->delete((int) $id);
        if ($result) {
            $this->flash('success', 'Đã xóa khách hàng.');
        } else {
            $this->flash('danger', 'Không thể xóa: khách hàng đã có lịch sử đơn hàng.');
        }
        $this->redirect('/customer/index');
    }

    public function history(?string $id = null): void
    {
        RoleMiddleware::require('admin', 'manager', 'cashier');
        $customer = new Customer();
        $data     = $customer->findById((int) $id);
        if (!$data) {
            $this->flash('danger', 'Không tìm thấy khách hàng.');
            $this->redirect('/customer/index');
        }
        $pageTitle = 'Lịch sử mua hàng — ' . $data['full_name'];
        $this->view('customers/history', [
            'customer' => $data,
            'history'  => $customer->getPurchaseHistory((int) $id),
        ]);
    }
}
