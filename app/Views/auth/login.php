<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập — Retail Chain</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm" style="width:100%;max-width:400px">
        <div class="card-body p-4">
            <h1 class="h4 mb-4 text-center">Retail Chain System</h1>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= BASE_URL ?>/auth/login">
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Mật khẩu</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
            <p class="small text-muted mt-3 mb-0 text-center">Test: admin@retailchain.vn / Abc@12345</p>
        </div>
    </div>
</body>
</html>
