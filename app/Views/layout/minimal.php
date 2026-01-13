<!doctype html>
<html lang="<?= service('request')->getLocale() ?>">
<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Offerte anfordern') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&family=Roboto+Slab:wght@400&display=swap" rel="stylesheet">

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
        :root {
            --color-primary: #6EC1E4;
            --color-secondary: #54595F;
            --color-text: #7A7A7A;
            --color-accent: #61CE70;
        }
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        h1, h2, h3, h4, h5, h6 {
            color: inherit;
            font-family: inherit;
            font-weight: 500;
            line-height: 1.2;
            margin-block-end: 1rem;
            margin-block-start: .5rem;
        }
        .btn-primary {
            color: rgba(249, 244, 255, 1);
            background-color: rgba(149, 92, 233, 1);
            border: 1px solid rgba(149, 92, 233, 1);
            border-radius: 0;
            padding: 15px 40px;
            font-size: 1rem;
            font-weight: 400;
            transition: all .3s;
        }
        .btn-primary:hover, .btn-primary:focus {
            color: rgba(149, 92, 233, 1);
            background-color: rgba(249, 244, 255, 1);
            border: 1px solid rgba(149, 92, 233, 1);
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
