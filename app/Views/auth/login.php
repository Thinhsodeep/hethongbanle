<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập — Retail Chain</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<div class="stripe-login-wrap">
    <div class="stripe-login-card">
        <div class="stripe-card">
            <div class="text-center mb-4">
                <div class="stripe-avatar stripe-avatar-dark mx-auto mb-3" style="border-radius:var(--radius-md);width:48px;height:48px;font-size:1rem">RC</div>
                <h1 class="h3 mb-1">Retail Chain</h1>
                <p class="subtext mb-0">Đăng nhập hệ thống quản lý</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="stripe-alert stripe-alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= BASE_URL ?>/auth/login">
                <div class="mb-3">
                    <label class="label-text mb-1" for="email">Email</label>
                    <input type="email" class="form-control stripe-input" id="email" name="email" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="label-text mb-1" for="password">Mật khẩu</label>
                    <input type="password" class="form-control stripe-input" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
            <p class="subtext text-center mt-3 mb-0">Test: admin@retailchain.vn / Abc@12345</p>
        </div>
    </div>
</div>
</body>
</html>
