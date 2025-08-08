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

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Logo -->
        <a class="navbar-brand fw-bold text-primary" href="/">
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
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= $segment1 === 'offers' ? 'active' : '' ?>" href="#" id="offersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Anfragen
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="offersDropdown">
                                <li><a class="dropdown-item" href="/offers">Offene Anfragen</a></li>
                                <li><a class="dropdown-item" href="/offers/mine">Gekaufte Anfragen</a></li>
                            </ul>
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
