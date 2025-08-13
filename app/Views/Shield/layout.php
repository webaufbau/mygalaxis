<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= $this->renderSection('title') ?> - <?= esc($title ?? siteconfig()->name) ?></title>

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <?= $this->renderSection('pageStyles') ?>

    <?php
    $mimeType = pathinfo(siteconfig()->faviconUrl, PATHINFO_EXTENSION) === 'jpg' ? 'image/jpeg' : 'image/png';
    ?>
    <link rel="shortcut icon" type="<?= $mimeType ?>" href="<?= siteconfig()->faviconUrl ?>">
    <link rel="apple-touch-icon" href="<?= siteconfig()->faviconUrl ?>">
</head>

<body class="bg-light">

    <main role="main" class="container">
        <?= $this->renderSection('main') ?>
    </main>

<?= $this->renderSection('pageScripts') ?>


    <footer class="bg-white border-top mt-5 py-3">
        <div class="container d-flex justify-content-center">
            <!-- Sprachumschalter -->
            <form method="get" action="" class="m-0">
                <?php
                $locales = ['de' => 'Deutsch', 'en' => 'English', 'fr' => 'Français', 'it' => 'Italiano'];
                $currentUri = service('uri')->getPath();
                $currentLocale = getCurrentLocale(array_keys($locales));
                ?>

                <select class="form-select form-select-sm" onchange="location = this.value;">
                    <?php foreach ($locales as $code => $name):
                        $url = base_url(changeLocaleInUri($currentUri, $code, array_keys($locales)));
                        $selected = ($code === $currentLocale) ? 'selected' : '';
                        ?>
                        <option value="<?= esc($url) ?>" <?= $selected ?>><?= esc($name) ?></option>
                    <?php endforeach; ?>
                </select>

            </form>
        </div>
    </footer>


</body>
</html>
