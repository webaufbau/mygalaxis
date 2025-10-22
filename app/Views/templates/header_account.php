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

    <?php
    $siteConfig = siteconfig();
    if($siteConfig->faviconUrl !== '') {
        $mimeType = pathinfo($siteConfig->faviconUrl, PATHINFO_EXTENSION) === 'jpg' ? 'image/jpeg' : 'image/png';
        ?>
        <link rel="shortcut icon" type="<?= $mimeType ?>" href="<?= $siteConfig->faviconUrl ?>">
        <link rel="apple-touch-icon" href="<?= $siteConfig->faviconUrl ?>">
    <?php } ?>
</head>
<body>

<!-- Role Indicator for Admins -->
<?php if (auth()->loggedIn()): ?>
    <?php
    $isAdmin = auth()->user()->inGroup('admin');
    $isFirm = auth()->user()->inGroup('user');
    $currentPath = service('uri')->getPath();

    // Admin-Bereich ist wenn /admin/ im Pfad vorkommt (inkl. /admin/dashboard)
    $isAdminArea = strpos($currentPath, 'admin/') !== false || strpos($currentPath, 'admin') === 0;
    ?>

    <?php if ($isAdmin): ?>
        <!-- Role Indicator Banner -->
        <div class="role-indicator-banner <?= $isAdminArea ? 'bg-danger' : '' ?>">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-badge me-2"></i>
                        <span class="fw-semibold">Angemeldet als:</span>
                        <span class="ms-2 badge <?= $isAdminArea ? 'bg-danger' : 'bg-primary' ?>">
                            <?php if ($isAdminArea): ?>
                                👨‍💼 Administrator
                            <?php elseif ($isFirm): ?>
                                🏢 Firma
                            <?php else: ?>
                                👨‍💼 Administrator
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($isFirm): ?>
                    <div class="role-switch">
                        <?php if ($isAdminArea): ?>
                            <a href="<?= site_url('dashboard') ?>" class="btn btn-sm btn-light">
                                <i class="bi bi-building me-1"></i>Zur Firmen-Ansicht wechseln
                            </a>
                        <?php else: ?>
                            <a href="/admin/user" class="btn btn-sm btn-light">
                                <i class="bi bi-shield-lock me-1"></i>Zur Admin-Ansicht wechseln
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <style>
        /* Firmen-Modus (Blau/Lila) */
        .role-indicator-banner {
            background: linear-gradient(135deg, #4A90E2 0%, #667eea 100%);
            color: white;
            border-bottom: 3px solid rgba(255,255,255,0.2);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Admin-Modus (Rot/Orange) */
        .role-indicator-banner.bg-danger {
            background: linear-gradient(135deg, #FF6B6B 0%, #ee5a6f 100%);
        }

        .role-indicator-banner .badge {
            font-size: 0.9rem;
            padding: 0.5em 1em;
            font-weight: 600;
            letter-spacing: 0.5px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.9; }
        }

        .role-switch .btn {
            font-size: 0.85rem;
            border: 2px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            color: white;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        .role-switch .btn:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Admin-Bereich: Navbar mit rotem Akzent */
        body.admin-mode .navbar {
            background: linear-gradient(to right, #fff 0%, #fff5f5 100%) !important;
            border-bottom: 3px solid #FF6B6B !important;
        }
        body.admin-mode .navbar-brand {
            color: #FF6B6B !important;
        }
        body.admin-mode .nav-link.active {
            color: #FF6B6B !important;
            border-bottom: 2px solid #FF6B6B;
        }
        body.admin-mode .btn-primary {
            background-color: #FF6B6B;
            border-color: #FF6B6B;
        }
        body.admin-mode .btn-primary:hover {
            background-color: #ee5a6f;
            border-color: #ee5a6f;
        }

        /* Firmen-Bereich: Navbar mit blauem Akzent */
        body.firm-mode .navbar {
            background: linear-gradient(to right, #fff 0%, #f0f7ff 100%) !important;
            border-bottom: 3px solid #4A90E2 !important;
        }
        body.firm-mode .navbar-brand {
            color: #4A90E2 !important;
        }
        body.firm-mode .nav-link.active {
            color: #4A90E2 !important;
            border-bottom: 2px solid #4A90E2;
        }
        body.firm-mode .btn-primary {
            background-color: #4A90E2;
            border-color: #4A90E2;
        }
        body.firm-mode .btn-primary:hover {
            background-color: #357ABD;
            border-color: #357ABD;
        }

        /* Text-Farben für bessere Lesbarkeit */
        body.admin-mode h1, body.admin-mode h2, body.admin-mode h3 {
            color: #2c3e50;
        }
        body.admin-mode .card {
            border-left: 4px solid #FF6B6B;
        }

        body.firm-mode .card {
            border-left: 4px solid #4A90E2;
        }

        /* Anpassung für Navbar */
        body.has-role-indicator {
            padding-top: 0;
        }

        /* Admin-Menü Anpassungen für bessere Platznutzung */
        body.admin-mode .navbar-nav .nav-link {
            font-size: 0.875rem;
            white-space: nowrap;
            padding: 0.5rem 0.5rem;
        }

        body.admin-mode .navbar-nav .nav-link i {
            font-size: 0.9rem;
        }

        /* Auf kleineren Bildschirmen noch kompakter */
        @media (max-width: 1600px) {
            body.admin-mode .navbar-nav .nav-link {
                font-size: 0.85rem;
                padding: 0.5rem 0.4rem;
            }
        }
    </style>
    <script>
        document.body.classList.add('has-role-indicator');
        <?php if ($isAdminArea): ?>
            document.body.classList.add('admin-mode');
        <?php else: ?>
            document.body.classList.add('firm-mode');
        <?php endif; ?>
    </script>
<?php endif; ?>

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm">
    <div class="container-xxl d-flex justify-content-between align-items-center">
        <!-- Logo -->
        <a class="navbar-brand fw-bold text-primary" href="<?= auth()->loggedIn() ? site_url('dashboard') : '/' ?>">
            <?=$siteConfig->name;?>
        </a>

        <?php if (auth()->loggedIn()): ?>
            <!-- Toggle Button for Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Navigation umschalten">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation & Logout -->
            <div class="collapse navbar-collapse justify-content-between" id="mainNav">
                <!-- Center Navigation -->
                <?php
                $segment1 = service('uri')->getSegment(1); // '' bei '/', 'dashboard', 'offers', etc.
                ?>

                <?php if(auth()->user()->inGroup('user')) { ?>
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?= ($segment1 === '' || $segment1 === 'dashboard') ? 'active' : '' ?>" href="/dashboard">XÜbersichtX</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $segment1 === 'filter' ? 'active' : '' ?>" href="/filter">Filter</a>
                        </li>
                        <!--<li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= $segment1 === 'offers' ? 'active' : '' ?>" href="#" id="offersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Anfragen
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="offersDropdown">
                                <li><a class="dropdown-item" href="/offers">Offene Anfragen</a></li>
                                <li><a class="dropdown-item" href="/offers/mine">Gekaufte Anfragen</a></li>
                            </ul>
                        </li>-->
                        <li class="nav-item">
                            <a class="nav-link <?= $segment1 === 'offers' ? 'active' : '' ?>" href="/offers">Anfragen</a>
                        </li>


                        <li class="nav-item">
                            <a class="nav-link <?= $segment1 === 'finance' ? 'active' : '' ?>" href="/finance">Finanzen</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $segment1 === 'agenda' ? 'active' : '' ?>" href="/agenda">Agenda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $segment1 === 'profile' ? 'active' : '' ?>" href="/profile">Mein Konto</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $segment1 === 'reviews' ? 'active' : '' ?>" href="/reviews">Bewertungen</a>
                        </li>
                    </ul>
                <?php } ?>


                <?php if(auth()->user()->inGroup('admin')) { ?>

                <?php } ?>



                <ul class="navbar-nav ms-auto">
                    <?php if(auth()->user()->inGroup('admin')) { ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($segment1 === '' || $segment1 === 'dashboard') ? 'active' : '' ?>" href="<?= site_url('dashboard') ?>">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($segment1 === 'admin' && service('uri')->getSegment(2) === 'user') ? 'active' : '' ?>" href="/admin/user">
                                <i class="bi bi-buildings me-1"></i> Firmen
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($segment1 === 'admin' && service('uri')->getSegment(2) === 'regions') ? 'active' : '' ?>" href="/admin/regions">
                                <i class="bi bi-geo-alt me-1"></i> Regionen
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($segment1 === 'admin' && service('uri')->getSegment(2) === 'review') ? 'active' : '' ?>" href="/admin/review">
                                <i class="bi bi-star me-1"></i> Bewertungen
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($segment1 === 'admin' && service('uri')->getSegment(2) === 'category') ? 'active' : '' ?>" href="/admin/category">
                                <i class="bi bi-list me-1"></i> Kategorien
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($segment1 === 'admin' && service('uri')->getSegment(2) === 'settings') ? 'active' : '' ?>" href="/admin/settings">
                                <i class="bi bi-gear me-1"></i> Einstellungen
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($segment1 === 'admin' && service('uri')->getSegment(2) === 'language-editor') ? 'active' : '' ?>" href="/admin/language-editor">
                                <i class="bi bi-translate me-1"></i> Texte
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($segment1 === 'admin' && service('uri')->getSegment(2) === 'email-templates') ? 'active' : '' ?>" href="/admin/email-templates">
                                <i class="bi bi-envelope me-1"></i> Templates
                            </a>
                        </li>

                    <?php } ?>
                    <!-- Logout Right -->
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/logout">
                            <i class="bi bi-box-arrow-right me-1"></i> Abmelden
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
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schliessen"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('warning')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schliessen"></button>
        </div>
    <?php endif; ?>
