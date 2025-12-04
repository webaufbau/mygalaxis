<?php
$siteConfig = siteconfig();
$currentPath = service('uri')->getPath();
$isAdminArea = auth()->loggedIn() && auth()->user()->inGroup('admin') &&
               (strpos($currentPath, 'admin/') !== false || strpos($currentPath, 'admin') === 0);

// Im Admin-Bereich: Sidebar-Layout verwenden
if ($isAdminArea):
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Admin - ' . $siteConfig->name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Eigene Styles -->
    <link rel="stylesheet" href="/css/app.css?v=<?=filemtime(FCPATH . 'css/app.css')?>">

    <style>
        :root {
            --sidebar-width: 220px;
            --sidebar-collapsed-width: 60px;
            --header-height: 50px;
            --admin-primary: #FF6B6B;
            --admin-primary-dark: #ee5a6f;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        .admin-wrapper {
            display: flex;
            height: 100vh;
        }

        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            color: #ecf0f1;
            display: flex;
            flex-direction: column;
            transition: width 0.3s ease;
            overflow: hidden;
            flex-shrink: 0;
        }

        .admin-sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 60px;
        }

        .sidebar-brand {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: #ecf0f1;
            cursor: pointer;
            padding: 0.25rem;
            opacity: 0.7;
        }

        .sidebar-toggle:hover {
            opacity: 1;
        }

        .collapsed .sidebar-brand,
        .collapsed .nav-label,
        .collapsed .nav-text,
        .collapsed .badge {
            display: none;
        }

        .collapsed .sidebar-toggle {
            margin: 0 auto;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem 0;
        }

        .nav-section {
            margin-bottom: 0.5rem;
        }

        .nav-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #7f8c8d;
            padding: 0.75rem 1rem 0.25rem;
            white-space: nowrap;
        }

        .sidebar-nav .nav-link {
            color: #f1f1f1;
            padding: 0.6rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            white-space: nowrap;
        }

        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }

        .sidebar-nav .nav-link.active {
            background: rgba(255,107,107,0.15);
            color: var(--admin-primary);
            border-left-color: var(--admin-primary);
        }

        .sidebar-nav .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-nav .badge {
            margin-left: auto;
            font-size: 0.7rem;
        }

        .collapsed .sidebar-nav .nav-link {
            justify-content: center;
            padding: 0.75rem;
        }

        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 0.5rem;
        }

        .sidebar-footer .nav-link {
            color: #e74c3c !important;
        }

        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #f8f9fa;
        }

        .admin-topbar {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: var(--header-height);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        @media (max-width: 991px) {
            .admin-sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                width: 100% !important;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s ease;
            }

            .admin-sidebar.show {
                left: 0;
            }

            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1040;
            }

            .sidebar-backdrop.show {
                display: block;
            }

            .mobile-toggle {
                display: block !important;
            }

            /* Sidebar Header auf Mobile grösser */
            .admin-sidebar .sidebar-header {
                padding: 1.5rem;
            }

            /* Navigation Links grösser auf Mobile */
            .admin-sidebar .sidebar-nav .nav-link {
                padding: 1rem 1.5rem;
                font-size: 1.1rem;
            }

            .admin-sidebar .nav-label {
                padding: 1rem 1.5rem 0.5rem;
                font-size: 0.8rem;
            }
        }

        @media (min-width: 992px) {
            .mobile-toggle {
                display: none !important;
            }
        }

        .admin-content .card {
            border-left: none;
        }
    </style>

    <?php if($siteConfig->faviconUrl !== ''):
        $mimeType = pathinfo($siteConfig->faviconUrl, PATHINFO_EXTENSION) === 'jpg' ? 'image/jpeg' : 'image/png';
    ?>
        <link rel="shortcut icon" type="<?= $mimeType ?>" href="<?= $siteConfig->faviconUrl ?>">
        <link rel="apple-touch-icon" href="<?= $siteConfig->faviconUrl ?>">
    <?php endif; ?>
</head>
<body>

<?php
$segment2 = service('uri')->getSegment(2);
$isFirm = auth()->user()->inGroup('user');

$pendingCount = (new \App\Models\OfferModel())
    ->where('verified', 1)
    ->where('companies_notified_at IS NULL')
    ->where('status', 'available')
    ->countAllResults();
?>

<div class="admin-wrapper">
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="/admin/dashboard" class="sidebar-brand"><?= esc($siteConfig->name) ?></a>
            <button class="sidebar-toggle d-none d-lg-block" id="sidebarToggle" title="Menü ein-/ausklappen">
                <i class="bi bi-list"></i>
            </button>
            <button class="sidebar-toggle d-lg-none" id="sidebarClose">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <!-- Haupt -->
            <div class="nav-section">
                <div class="nav-label">Übersicht</div>
                <a class="nav-link <?= $segment2 === 'dashboard' ? 'active' : '' ?>" href="/admin/dashboard">
                    <i class="bi bi-inbox"></i>
                    <span class="nav-text">Anfragen</span>
                </a>
                <a class="nav-link <?= $segment2 === 'user' ? 'active' : '' ?>" href="/admin/user">
                    <i class="bi bi-buildings"></i>
                    <span class="nav-text">Firmen</span>
                </a>
            </div>

            <!-- Inhalte -->
            <div class="nav-section">
                <div class="nav-label">Inhalte</div>
                <a class="nav-link <?= $segment2 === 'regions' ? 'active' : '' ?>" href="/admin/regions">
                    <i class="bi bi-geo-alt"></i>
                    <span class="nav-text">Regionen</span>
                </a>
                <a class="nav-link <?= $segment2 === 'category' ? 'active' : '' ?>" href="/admin/category">
                    <i class="bi bi-list"></i>
                    <span class="nav-text">Kategorien</span>
                </a>
                <a class="nav-link <?= $segment2 === 'review' ? 'active' : '' ?>" href="/admin/review">
                    <i class="bi bi-star"></i>
                    <span class="nav-text">Bewertungen</span>
                </a>
                <a class="nav-link <?= $segment2 === 'referrals' ? 'active' : '' ?>" href="/admin/referrals">
                    <i class="bi bi-people"></i>
                    <span class="nav-text">Empfehlungen</span>
                </a>
            </div>

            <!-- System -->
            <div class="nav-section">
                <div class="nav-label">System</div>
                <a class="nav-link <?= $segment2 === 'settings' ? 'active' : '' ?>" href="/admin/settings">
                    <i class="bi bi-gear"></i>
                    <span class="nav-text">Einstellungen</span>
                </a>
                <a class="nav-link <?= $segment2 === 'language-editor' ? 'active' : '' ?>" href="/admin/language-editor">
                    <i class="bi bi-translate"></i>
                    <span class="nav-text">Texte</span>
                </a>
                <a class="nav-link <?= $segment2 === 'email-templates' ? 'active' : '' ?>" href="/admin/email-templates">
                    <i class="bi bi-envelope"></i>
                    <span class="nav-text">E-Mail Templates</span>
                </a>
                <a class="nav-link <?= $segment2 === 'email-log' ? 'active' : '' ?>" href="/admin/email-log">
                    <i class="bi bi-envelope-paper"></i>
                    <span class="nav-text">E-Mail Verlauf</span>
                </a>
                <a class="nav-link <?= $segment2 === 'invoices' ? 'active' : '' ?>" href="/admin/invoices">
                    <i class="bi bi-file-earmark-text"></i>
                    <span class="nav-text">Rechnungen</span>
                </a>
                <a class="nav-link <?= $segment2 === 'trash' ? 'active' : '' ?>" href="/admin/trash">
                    <i class="bi bi-trash"></i>
                    <span class="nav-text">Papierkorb</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <?php if ($isFirm): ?>
                <a class="nav-link" href="/offers" style="color: #3498db !important;">
                    <i class="bi bi-building"></i>
                    <span class="nav-text">Firmen-Ansicht</span>
                </a>
            <?php endif; ?>
            <a class="nav-link" href="/logout">
                <i class="bi bi-box-arrow-right"></i>
                <span class="nav-text">Abmelden</span>
            </a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="btn btn-sm btn-outline-secondary mobile-toggle" id="mobileToggle">
                    <i class="bi bi-list"></i>
                </button>
                <span class="text-muted small">
                    <i class="bi bi-shield-lock text-danger me-1"></i> Admin-Bereich
                </span>
            </div>
            <div class="topbar-right">
                <span class="text-muted small d-none d-md-inline">
                    <?= esc(auth()->user()->username) ?>
                </span>
            </div>
        </header>

        <main class="admin-content">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?= esc(session()->getFlashdata('success')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= esc(session()->getFlashdata('error')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('warning')): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i><?= esc(session()->getFlashdata('warning')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

<?php else: ?>
<!-- Firmen-Bereich: Standard-Layout -->
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? $siteConfig->name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery & Bootstrap Bundle JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Eigene Styles -->
    <link rel="stylesheet" href="/css/app.css?v=<?=filemtime(FCPATH . 'css/app.css')?>">

    <style>
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

    <?php if($siteConfig->faviconUrl !== ''):
        $mimeType = pathinfo($siteConfig->faviconUrl, PATHINFO_EXTENSION) === 'jpg' ? 'image/jpeg' : 'image/png';
    ?>
        <link rel="shortcut icon" type="<?= $mimeType ?>" href="<?= $siteConfig->faviconUrl ?>">
        <link rel="apple-touch-icon" href="<?= $siteConfig->faviconUrl ?>">
    <?php endif; ?>

    <?php if (env('CI_ENVIRONMENT') === 'production'): ?>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-NYR3ZB836N"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-NYR3ZB836N', { 'anonymize_ip': true });
        </script>

        <!-- Meta Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src='https://connect.facebook.net/en_US/fbevents.js';
            s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script');
            fbq('init', '696909980088468');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=696909980088468&ev=PageView&noscript=1"/></noscript>
    <?php endif; ?>
</head>
<body>

<?php if (auth()->loggedIn()): ?>
    <?php
    $isAdmin = auth()->user()->inGroup('admin');
    $isFirm = auth()->user()->inGroup('user');
    ?>

    <?php if ($isAdmin): ?>
        <div class="role-indicator-banner">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-badge me-2"></i>
                        <span class="fw-semibold">Angemeldet als:</span>
                        <span class="ms-2 badge bg-primary">🏢 Firma</span>
                    </div>
                    <div class="role-switch">
                        <a href="/admin/user" class="btn btn-sm btn-light">
                            <i class="bi bi-shield-lock me-1"></i>Zur Admin-Ansicht wechseln
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <style>
        .role-indicator-banner {
            background: linear-gradient(135deg, #4A90E2 0%, #667eea 100%);
            color: white;
            border-bottom: 3px solid rgba(255,255,255,0.2);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .role-indicator-banner .badge {
            font-size: 0.9rem;
            padding: 0.5em 1em;
            font-weight: 600;
        }
        .role-switch .btn {
            font-size: 0.85rem;
            border: 2px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            color: white;
            font-weight: 600;
        }
        .role-switch .btn:hover {
            background: white;
            color: #667eea;
        }
        body .navbar {
            background: linear-gradient(to right, #fff 0%, #f0f7ff 100%) !important;
            border-bottom: 3px solid #4A90E2 !important;
        }
        body .navbar-brand {
            color: #4A90E2 !important;
        }
        body .nav-link.active {
            color: #4A90E2 !important;
            border-bottom: 2px solid #4A90E2;
        }
        body .card {
            border-left: 4px solid #4A90E2;
        }
    </style>
<?php endif; ?>

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm">
    <div class="container-xxl d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold text-primary" href="<?= auth()->loggedIn() ? site_url('offers') : '/' ?>">
            <?= $siteConfig->name ?>
        </a>

        <?php if (auth()->loggedIn()): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Navigation umschalten">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-between" id="mainNav">
                <?php
                $segment1 = service('uri')->getSegment(1);
                if(auth()->user()->inGroup('user')):
                ?>
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?= ($segment1 === '' || $segment1 === 'offers') ? 'active' : '' ?>" href="/offers">Anfragen</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $segment1 === 'filter' ? 'active' : '' ?>" href="/filter">Filter</a>
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
                <?php endif; ?>

                <ul class="navbar-nav ms-auto">
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
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('warning')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

<?php endif; ?>
