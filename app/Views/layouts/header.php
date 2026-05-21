<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$pageTitle = $pageTitle ?? 'Retail Chain System';
$flashStripeClass = match ($flash['type'] ?? '') {
    'success' => 'stripe-alert-success',
    'danger'  => 'stripe-alert-danger',
    'warning' => 'stripe-alert-warning',
    default   => 'stripe-alert-info',
};
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css?v=<?= (int) (@filemtime(APP_ROOT . '/public/css/style.css') ?: 1) ?>">
</head>
<body>
<div class="d-flex" style="min-height:100vh">
<?php require_once __DIR__ . '/sidebar.php'; ?>
<main class="stripe-main">
<?php if ($flash): ?>
    <div class="stripe-alert <?= htmlspecialchars($flashStripeClass) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
