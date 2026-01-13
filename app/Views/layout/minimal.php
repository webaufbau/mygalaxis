<!doctype html>
<html lang="<?= service('request')->getLocale() ?>">
<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Offerte anfordern') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <?php
    $siteConfigLayout = siteconfig();
    if($siteConfigLayout->faviconUrl !== '') {
        $mimeType = pathinfo($siteConfigLayout->faviconUrl, PATHINFO_EXTENSION) === 'jpg' ? 'image/jpeg' : 'image/png';
        ?>
        <link rel="shortcut icon" type="<?= $mimeType ?>" href="<?= $siteConfigLayout->faviconUrl ?>">
        <link rel="apple-touch-icon" href="<?= $siteConfigLayout->faviconUrl ?>">
    <?php } ?>

    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>

<main>
    <?= $this->renderSection('content') ?>
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
