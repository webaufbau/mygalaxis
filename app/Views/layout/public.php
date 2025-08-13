<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? siteconfig()->name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery & Bootstrap Bundle JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Eigene Styles -->
    <link rel="stylesheet" href="/css/app.css?v=<?=filemtime(FCPATH . 'css/app.css')?>">

    <style>
        /* Sticky footer styles */
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
        }
    </style>
</head>
<body>

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Logo -->
        <a class="navbar-brand fw-bold text-primary" href="/">
            <?=$siteConfig->name;?>
        </a>

    </div>
</nav>


<main class="container mb-5">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schliessen"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schliessen"></button>
        </div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</main>


<!-- Footer -->
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




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script type="text/javascript">

    $(".del").click(function(){
        if (!confirm("Wirklich löschen?")){
            return false;
        }
    });

    $(".cancel").click(function(){
        if (!confirm("Wirklich abbrechen?")){
            return false;
        }
    });

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el, {
            placement: 'bottom'
        });
    });

</script>

</body>
</html>
