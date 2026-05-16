<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$pageTitle = $pageTitle ?? 'Retail Chain System';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<div class="d-flex">
<?php require_once __DIR__ . '/sidebar.php'; ?>
<div class="flex-grow-1 p-4">
<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
