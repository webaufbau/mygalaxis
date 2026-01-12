<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Admin - ' . siteconfig()->name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery & Bootstrap Bundle JS -->
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

        /* Admin Layout Container */
        .admin-wrapper {
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
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

        /* Navigation */
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

        /* Sidebar Footer */
        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 0.5rem;
        }

        .sidebar-footer .nav-link {
            color: #e74c3c !important;
        }

        /* Main Content Area */
        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #f8f9fa;
        }

        /* Top Bar */
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

        /* Content Area */
        .admin-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        /* Mobile Responsive */
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

        /* Cards ohne linken Border im Admin */
        .admin-content .card {
            border-left: none;
        }

        /* ========== PRINT STYLES ========== */
        @media print {
            /* Sidebar und Topbar ausblenden */
            .admin-sidebar,
            .sidebar-backdrop,
            .admin-topbar,
            .mobile-toggle {
                display: none !important;
            }

            /* Body/HTML Overflow zurücksetzen */
            html, body {
                height: auto !important;
                overflow: visible !important;
            }

            /* Wrapper als Block statt Flex */
            .admin-wrapper {
                display: block !important;
                height: auto !important;
            }

            /* Main Content volle Breite */
            .admin-main {
                width: 100% !important;
                overflow: visible !important;
            }

            /* Content Area ohne Scroll */
            .admin-content {
                overflow: visible !important;
                padding: 0 !important;
                height: auto !important;
            }

            /* DataTables: Pagination, Search, Info ausblenden */
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate,
            .dt-buttons,
            .dataTables_processing {
                display: none !important;
            }

            /* Tabelle über mehrere Seiten umbrechen */
            table {
                page-break-inside: auto !important;
            }

            tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }

            thead {
                display: table-header-group !important;
            }

            tfoot {
                display: table-footer-group !important;
            }

            /* Tabelle volle Breite */
            .table-responsive {
                overflow: visible !important;
            }

            table.dataTable {
                width: 100% !important;
                font-size: 9pt !important;
            }

            /* Buttons ausblenden */
            .btn,
            form[onsubmit*="confirm"] {
                display: none !important;
            }

            /* Filter-Formular ausblenden */
            form.row.g-3.mb-4 {
                display: none !important;
            }

            /* Alerts ausblenden */
            .alert {
                display: none !important;
            }

            /* Kleinere Schrift und kompaktere Zellen */
            #offersTable td,
            #offersTable th {
                padding: 2px 4px !important;
                font-size: 8pt !important;
            }

            /* Badges kompakter */
            .badge {
                padding: 1px 4px !important;
                font-size: 7pt !important;
            }

            /* Seitenränder */
            @page {
                margin: 1cm;
                size: landscape;
            }
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

<?php
$segment2 = service('uri')->getSegment(2);
$segment3 = service('uri')->getSegment(3);
$isFirm = auth()->user()->inGroup('user');

// Pending offers count
$pendingCount = (new \App\Models\OfferModel())
    ->where('verified', 1)
    ->where('companies_notified_at IS NULL')
    ->where('status', 'available')
    ->countAllResults();
?>

<div class="admin-wrapper">
    <!-- Sidebar Backdrop (Mobile) -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Sidebar -->
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
                <?php /* Temporär deaktiviert - wird später aktiviert
                <a class="nav-link <?= $segment2 === 'offers' ? 'active' : '' ?>" href="/admin/offers/pending">
                    <i class="bi bi-inbox"></i>
                    <span class="nav-text">Anfragen</span>
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge bg-warning text-dark"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
                */ ?>
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
                    <i class="bi bi-tags"></i>
                    <span class="nav-text">Branchen</span>
                </a>
                <a class="nav-link <?= $segment2 === 'projects' ? 'active' : '' ?>" href="/admin/projects">
                    <i class="bi bi-kanban"></i>
                    <span class="nav-text">Projekte</span>
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
                <a class="nav-link <?= $segment2 === 'import-export' ? 'active' : '' ?>" href="/admin/import-export">
                    <i class="bi bi-arrow-down-up"></i>
                    <span class="nav-text">Import/Export</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <?php if ($isFirm): ?>
                <a class="nav-link" href="/offers">
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

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Top Bar -->
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

        <!-- Content -->
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

            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebarClose = document.getElementById('sidebarClose');

    // Desktop: Collapse toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });

        // Restore state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }
    }

    // Mobile: Show sidebar
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.add('show');
            backdrop.classList.add('show');
        });
    }

    // Mobile: Hide sidebar
    function closeMobileSidebar() {
        sidebar.classList.remove('show');
        backdrop.classList.remove('show');
    }

    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeMobileSidebar);
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeMobileSidebar);
    }

    // Delete confirmation
    document.querySelectorAll('.del').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm('Wirklich löschen?')) {
                e.preventDefault();
            }
        });
    });

    // Tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
});
</script>

</body>
</html>
