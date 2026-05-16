<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Retail Chain') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/css/style.css') ?>">
</head>
<body>
    <?php require __DIR__ . '/header.php'; ?>
    <?php require __DIR__ . '/sidebar.php'; ?>
    <main class="main"><?= $content ?? '' ?></main>
    <?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
