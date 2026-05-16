<?php

declare(strict_types=1);

require_once APP_ROOT . '/app/Middlewares/RoleMiddleware.php';
require_once APP_ROOT . '/app/Models/Product.php';

class ProductController extends Controller
{
    public function index(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $product = new Product();
        $pageTitle = 'Sản phẩm';
        $this->view('products/index', [
            'products'   => $product->search($_GET['kw'] ?? '', (int) ($_GET['cat'] ?? 0) ?: null),
            'categories' => $product->getAllCategories(),
            'kw'         => $_GET['kw'] ?? '',
            'cat'        => (int) ($_GET['cat'] ?? 0),
        ]);
    }

    public function create(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $pageTitle = 'Thêm sản phẩm';
        $this->view('products/form', [
            'product'    => null,
            'categories' => (new Product())->getAllCategories(),
        ]);
    }

    public function store(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $data = $_POST;
        $data = $this->handleImageUpload($data);

        $model     = new Product();
        $productId = $model->create($data);
        $model->initInventoryAllBranches($productId);

        $this->flash('success', 'Thêm sản phẩm thành công.');
        $this->redirect('/product/index');
    }

    public function edit(?string $id): void
    {
        RoleMiddleware::require('admin', 'manager');
        $model = new Product();
        $product = $model->findById((int) $id);
        if (!$product) {
            $this->redirect('/product/index');
        }
        $pageTitle = 'Sửa sản phẩm';
        $this->view('products/form', [
            'product'    => $product,
            'categories' => $model->getAllCategories(),
        ]);
    }

    public function update(?string $id): void
    {
        RoleMiddleware::require('admin', 'manager');
        $data  = $_POST;
        $model = new Product();
        $data  = $this->handleImageUpload($data);
        if (!empty($data['image'])) {
            $model->updateImage((int) $id, $data['image']);
            unset($data['image']);
        }
        $model->update((int) $id, $data);
        $this->flash('success', 'Cập nhật sản phẩm thành công.');
        $this->redirect('/product/index');
    }

    public function categories(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $pageTitle = 'Danh mục sản phẩm';
        $this->view('products/categories', [
            'categories' => (new Product())->getAllCategories(),
        ]);
    }

    public function storeCategory(): void
    {
        RoleMiddleware::require('admin', 'manager');
        (new Product())->createCategory($_POST);
        $this->flash('success', 'Thêm danh mục thành công.');
        $this->redirect('/product/categories');
    }

    private function handleImageUpload(array $data): array
    {
        if (empty($_FILES['image']['name'])) {
            return $data;
        }
        $dir = APP_ROOT . '/public/images/products/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . time() . '.' . $ext;
        $dest     = $dir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $data['image'] = 'images/products/' . $filename;
        }
        return $data;
    }
}
