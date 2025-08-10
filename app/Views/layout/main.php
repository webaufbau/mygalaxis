<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Offerten Manager') ?></title>
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

<?php
$locales = ['de' => 'Deutsch', 'en' => 'English', 'fr' => 'Français', 'it' => 'Italiano'];
$currentUri = service('uri')->getPath();
$currentLocale = getCurrentLocale(array_keys($locales));
if ($currentLocale !== 'de') {
    // Sprache ist z.B. en, fr, it → Menüsegment ist 2. URI Segment
    $segment1 = service('uri')->getSegment(2);
} else {
    // Deutsch, keine Sprachkennung → Menüsegment ist 1. Segment
    $segment1 = service('uri')->getSegment(1);
}
?>

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Logo -->
        <a class="navbar-brand fw-bold text-primary" href="<?= lang_url('login') ?>">
            <?= esc(Config('SiteConfig')->name) ?>
        </a>

        <?php if (auth()->loggedIn()): ?>
            <!-- Toggle Button for Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="<?= esc(lang('General.toggleNavigation')) ?>">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation & Logout -->
            <div class="collapse navbar-collapse justify-content-between" id="mainNav">
                <?php if (auth()->user()->inGroup('user')): ?>
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?= ($segment1 === '' || $segment1 === 'dashboard') ? 'active' : '' ?>" href="<?= site_url('dashboard') ?>">
                                <?= esc(lang('Navigation.overview')) ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $segment1 === 'filter' ? 'active' : '' ?>" href="<?= site_url('filter') ?>">
                                <?= esc(lang('Navigation.filter')) ?>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= $segment1 === 'offers' ? 'active' : '' ?>" href="#" id="offersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= esc(lang('Navigation.requests')) ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="offersDropdown">
                                <li><a class="dropdown-item" href="<?= site_url('offers') ?>"><?= esc(lang('Navigation.openRequests')) ?></a></li>
                                <li><a class="dropdown-item" href="<?= site_url('offers/mine') ?>"><?= esc(lang('Navigation.purchasedRequests')) ?></a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $segment1 === 'finance' ? 'active' : '' ?>" href="<?= site_url('finance') ?>">
                                <?= esc(lang('Navigation.finance')) ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $segment1 === 'agenda' ? 'active' : '' ?>" href="<?= site_url('agenda') ?>">
                                <?= esc(lang('Navigation.agenda')) ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $segment1 === 'profile' ? 'active' : '' ?>" href="<?= site_url('profile') ?>">
                                <?= esc(lang('Navigation.myAccount')) ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $segment1 === 'reviews' ? 'active' : '' ?>" href="<?= site_url('reviews') ?>">
                                <?= esc(lang('Navigation.reviews')) ?>
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>



                <ul class="navbar-nav ms-auto">
                    <?php if(auth()->user()->inGroup('admin')) { ?>
                    <li class="nav-item">
                        <a class="nav-link text-normal" href="/admin/user">
                            <i class="bi bi-buildings me-1"></i> Firmen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-normal" href="/admin/review">
                            <i class="bi bi-star me-1"></i> Bewertungen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-normal" href="/admin/category">
                            <i class="bi bi-list me-1"></i> Kategorien
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-normal" href="/admin/settings">
                            <i class="bi bi-gear me-1"></i> Einstellungen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-normal" href="/admin/language-editor">
                            <i class="bi bi-translate me-1"></i> Texte
                        </a>
                    </li>

                    <?php } ?>
                    <!-- Logout Right -->
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/logout">
                            <i class="bi bi-box-arrow-right me-1"></i> <?= esc(lang('Navigation.logout')) ?>
                        </a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
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
<?php if(!auth()->loggedIn()): ?>
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
<?php endif; ?>



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
