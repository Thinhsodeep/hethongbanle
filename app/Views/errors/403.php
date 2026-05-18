<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Không có quyền</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body class="stripe-login-wrap">
    <div class="stripe-card text-center stripe-login-card">
        <h1 class="display-4 mb-2" style="color:var(--color-danger)">403</h1>
        <p class="subtext mb-4">Bạn không có quyền truy cập trang này.</p>
        <?php
        $role = $_SESSION['role'] ?? '';
        if (in_array($role, ['admin','manager'], true)) {
            $homeUrl = BASE_URL . '/admin/dashboard';
            $homeLabel = 'Về Dashboard';
        } elseif ($role === 'staff') {
            $homeUrl = BASE_URL . '/inventory/index';
            $homeLabel = 'Về Tồn kho';
        } else {
            $homeUrl = BASE_URL . '/pos/index';
            $homeLabel = 'Về Bán hàng';
        }
        ?>
        <a href="<?= htmlspecialchars($homeUrl) ?>" class="btn btn-primary"><?= $homeLabel ?></a>

    </div>
</body>
</html>
