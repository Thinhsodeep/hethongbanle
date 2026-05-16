<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Không có quyền</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="text-center">
        <h1 class="display-4 text-danger">403</h1>
        <p class="lead">Bạn không có quyền truy cập trang này.</p>
        <a href="<?= htmlspecialchars(BASE_URL . '/admin/dashboard') ?>" class="btn btn-primary">Về trang chủ</a>
    </div>
</body>
</html>
