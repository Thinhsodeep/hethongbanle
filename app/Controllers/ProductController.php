<?php

declare(strict_types=1);

require_once APP_ROOT . '/app/Middlewares/RoleMiddleware.php';
require_once APP_ROOT . '/app/Models/Product.php';

class ProductController extends Controller
{
    // ─── Danh sách sản phẩm ───────────────────────────────────────────
    public function index(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $product   = new Product();
        $pageTitle = 'Sản phẩm';
        $this->view('products/index', [
            'products'   => $product->getAll($_GET['kw'] ?? '', (int) ($_GET['cat'] ?? 0) ?: null),
            'categories' => $product->getAllCategories(),
            'kw'         => $_GET['kw'] ?? '',
            'cat'        => (int) ($_GET['cat'] ?? 0),
        ]);
    }

    // ─── Thêm sản phẩm ────────────────────────────────────────────────
    public function create(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $pageTitle = 'Thêm sản phẩm';
        $this->view('products/form', [
            'product'    => null,
            'variants'   => [],
            'categories' => (new Product())->getAllCategories(),
        ]);
    }

    public function store(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $data  = $_POST;
        $data  = $this->handleImageUpload($data);
        $model = new Product();

        // 1. Tạo product
        $productId = $model->create($data);

        // 2. Tạo variants (có thể nhiều dòng gửi lên)
        $skus          = $_POST['sku']          ?? [];
        $barcodes      = $_POST['barcode']      ?? [];
        $colors        = $_POST['color']        ?? [];
        $sizes         = $_POST['size']         ?? [];
        $attributes    = $_POST['attribute']    ?? [];
        $sellPrices    = $_POST['sell_price']   ?? [];
        $importPrices  = $_POST['import_price'] ?? [];
        $variantStatus = $_POST['vstatus']      ?? [];

        foreach ($skus as $k => $sku) {
            if (trim($sku) === '') continue;
            $variantId = $model->createVariant($productId, [
                'sku'          => trim($sku),
                'barcode'      => $barcodes[$k]     ?? '',
                'color'        => $colors[$k]       ?? '',
                'size'         => $sizes[$k]        ?? '',
                'attribute'    => $attributes[$k]   ?? '',
                'sell_price'   => (float) ($sellPrices[$k]   ?? 0),
                'import_price' => (float) ($importPrices[$k] ?? 0),
                'status'       => $variantStatus[$k] ?? 'active',
            ]);
            $model->initInventoryAllBranches($variantId);
        }

        $this->flash('success', 'Thêm sản phẩm thành công.');
        $this->redirect('/product/edit/' . $productId);
    }

    // ─── Sửa sản phẩm ────────────────────────────────────────────────
    public function edit(?string $id): void
    {
        RoleMiddleware::require('admin', 'manager');
        $model   = new Product();
        $product = $model->findById((int) $id);
        if (!$product) {
            $this->redirect('/product/index');
        }
        $pageTitle = 'Sửa sản phẩm: ' . $product['name'];
        $this->view('products/form', [
            'product'    => $product,
            'variants'   => $model->getVariants((int) $id),
            'categories' => $model->getAllCategories(),
        ]);
    }

    public function update(?string $id): void
    {
        RoleMiddleware::require('admin', 'manager');
        $data  = $_POST;
        $model = new Product();

        // Upload ảnh
        $data = $this->handleImageUpload($data);
        if (!empty($data['image'])) {
            $model->updateImage((int) $id, $data['image']);
            unset($data['image']);
        }

        // Cập nhật thông tin chung product
        $model->update((int) $id, $data);

        // Cập nhật variants hiện có
        $variantIds    = $_POST['variant_id']   ?? [];
        $skus          = $_POST['sku']          ?? [];
        $barcodes      = $_POST['barcode']      ?? [];
        $colors        = $_POST['color']        ?? [];
        $sizes         = $_POST['size']         ?? [];
        $attributes    = $_POST['attribute']    ?? [];
        $sellPrices    = $_POST['sell_price']   ?? [];
        $importPrices  = $_POST['import_price'] ?? [];
        $variantStatus = $_POST['vstatus']      ?? [];

        foreach ($variantIds as $k => $vid) {
            $vid = (int) $vid;
            if ($vid <= 0 || trim($skus[$k] ?? '') === '') continue;
            $model->updateVariant($vid, [
                'sku'          => trim($skus[$k]),
                'barcode'      => $barcodes[$k]     ?? '',
                'color'        => $colors[$k]       ?? '',
                'size'         => $sizes[$k]        ?? '',
                'attribute'    => $attributes[$k]   ?? '',
                'sell_price'   => (float) ($sellPrices[$k]   ?? 0),
                'import_price' => (float) ($importPrices[$k] ?? 0),
                'status'       => $variantStatus[$k] ?? 'active',
            ]);
        }

        // Thêm variants mới (variant_id = 0 hoặc rỗng)
        $newSkus         = $_POST['new_sku']          ?? [];
        $newBarcodes     = $_POST['new_barcode']      ?? [];
        $newColors       = $_POST['new_color']        ?? [];
        $newSizes        = $_POST['new_size']         ?? [];
        $newAttributes   = $_POST['new_attribute']   ?? [];
        $newSellPrices   = $_POST['new_sell_price']  ?? [];
        $newImportPrices = $_POST['new_import_price'] ?? [];
        $newStatuses     = $_POST['new_vstatus']      ?? [];

        foreach ($newSkus as $k => $sku) {
            if (trim($sku) === '') continue;
            $variantId = $model->createVariant((int) $id, [
                'sku'          => trim($sku),
                'barcode'      => $newBarcodes[$k]     ?? '',
                'color'        => $newColors[$k]       ?? '',
                'size'         => $newSizes[$k]        ?? '',
                'attribute'    => $newAttributes[$k]   ?? '',
                'sell_price'   => (float) ($newSellPrices[$k]   ?? 0),
                'import_price' => (float) ($newImportPrices[$k] ?? 0),
                'status'       => $newStatuses[$k] ?? 'active',
            ]);
            $model->initInventoryAllBranches($variantId);
        }

        $this->flash('success', 'Cập nhật sản phẩm thành công.');
        $this->redirect('/product/edit/' . $id);
    }

    // ─── Xóa variant (AJAX POST) ──────────────────────────────────────
    public function deleteVariant(?string $id): void
    {
        RoleMiddleware::require('admin', 'manager');
        $model = new Product();
        $model->deleteVariant((int) $id);
        $this->flash('success', 'Đã xóa biến thể.');
        // Redirect về trang edit của product
        $productId = (int) ($_POST['product_id'] ?? 0);
        $this->redirect($productId ? '/product/edit/' . $productId : '/product/index');
    }

    // ─── Danh mục ─────────────────────────────────────────────────────
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

    // ─── Helpers ──────────────────────────────────────────────────────
    private function handleImageUpload(array $data): array
    {
        if (empty($_FILES['image']['name'])) {
            return $data;
        }
        $dir = APP_ROOT . '/public/images/products/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = 'product_' . time() . '.' . $ext;
        $dest     = $dir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $data['image'] = 'images/products/' . $filename;
        }
        return $data;
    }
}
