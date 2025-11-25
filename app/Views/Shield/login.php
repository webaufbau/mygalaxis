<?php
$siteConfig = siteconfig();
$siteName = $siteConfig->name ?? 'MyGalaxis';
$logoUrl = $siteConfig->logoUrl ?? '';
$logoHeight = $siteConfig->logoHeightPixel ?? '40';

// Farbe aus SiteConfig holen
$primaryColor = $siteConfig->headerBackgroundColor ?? '#0d6efd';

// Sekundärfarbe berechnen (etwas dunkler)
function darkenColor($hex, $percent = 20) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r - ($r * $percent / 100)));
    $g = max(0, min(255, $g - ($g * $percent / 100)));
    $b = max(0, min(255, $b - ($b * $percent / 100)));

    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

$secondaryColor = darkenColor($primaryColor, 20);
?>
<!DOCTYPE html>
<html lang="<?= service('request')->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= lang('Auth.loginTitle') ?> - <?= esc($siteName) ?></title>
    <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/img/favicon.png') ?>"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <style>
        :root {
            --gradient: linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $secondaryColor ?> 100%);
            --primary-color: <?= $primaryColor ?>;
            --secondary-color: <?= $secondaryColor ?>;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .split-screen {
            display: flex;
            min-height: 100vh;
        }

        .form-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
            background: #ffffff;
        }

        .form-container {
            width: 100%;
            max-width: 450px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 2rem;
            text-decoration: none;
        }

        .logo:hover {
            color: var(--primary-color);
        }

        .logo i {
            font-size: 2rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: #1a1a1a;
        }

        .subtitle {
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle .toggle-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }

        .btn-primary {
            padding: 0.875rem 1rem;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            background: var(--gradient);
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #6c757d;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }

        .divider span {
            padding: 0 1rem;
            font-size: 0.875rem;
        }

        .benefits-side {
            flex: 1;
            background: var(--gradient);
            color: white;
            padding: 4rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .benefits-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .benefits-content {
            max-width: 500px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .benefits-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .benefits-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 3rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }

        .feature-list li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            font-size: 1.05rem;
        }

        .feature-list li i {
            font-size: 1.5rem;
            opacity: 0.9;
        }

        @media (max-width: 991px) {
            .split-screen {
                flex-direction: column;
            }

            .benefits-side {
                padding: 2rem 1.5rem;
            }

            .benefits-title {
                font-size: 1.75rem;
            }

            .benefits-subtitle {
                font-size: 1rem;
            }

            .form-side {
                padding: 2rem 1.5rem;
            }
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .language-switcher {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
        }

        .language-switcher select {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            background: white;
        }
    </style>

    <?php if (env('CI_ENVIRONMENT') === 'production'): ?>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-NYR3ZB836N"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            // Basis-Konfiguration
            gtag('config', 'G-NYR3ZB836N', {
                'anonymize_ip': true // IP-Anonymisierung
            });
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

            fbq('init', '696909980088468'); // Deine Pixel-ID
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id=696909980088468&ev=PageView&noscript=1"/>
        </noscript>
        <!-- End Meta Pixel Code -->
    <?php endif; ?>
</head>
<body>

<!-- Language Switcher -->
<?php
$locales = ['de' => 'Deutsch', 'en' => 'English', 'fr' => 'Français', 'it' => 'Italiano'];
$currentLocale = service('request')->getLocale();
$currentUri = service('uri')->getPath();
?>
<div class="language-switcher">
    <select class="form-select form-select-sm" onchange="location = this.value;">
        <?php foreach ($locales as $code => $name): ?>
            <?php
            $url = $code === 'de' ? site_url('login') : site_url($code . '/login');
            ?>
            <option value="<?= $url ?>" <?= $currentLocale === $code ? 'selected' : '' ?>>
                <?= $name ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="split-screen">
    <!-- Left Side - Form -->
    <div class="form-side">
        <div class="form-container">
            <a href="<?= site_url('/') ?>" class="logo">
                <i class="bi bi-tools"></i>
                <span><?= esc($siteName) ?></span>
            </a>

            <h1><?= lang('Auth.loginWelcome') ?></h1>
            <p class="subtitle"><?= lang('Auth.loginSubtitle') ?></p>

            <!-- Error Messages -->
            <?php if (session('error') !== null) : ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?= session('error') ?>
                </div>
            <?php elseif (session('errors') !== null) : ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?php if (is_array(session('errors'))) : ?>
                        <?php foreach (session('errors') as $error) : ?>
                            <?= $error ?><br>
                        <?php endforeach ?>
                    <?php else : ?>
                        <?= session('errors') ?>
                    <?php endif ?>
                </div>
            <?php endif ?>

            <!-- Success Message -->
            <?php if (session('message') !== null) : ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle"></i> <?= session('message') ?>
                </div>
            <?php endif ?>

            <form action="<?= lang_url('login') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label"><?= lang('Auth.email') ?></label>
                    <input type="email" class="form-control <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" id="email" name="email" placeholder="<?= lang('Auth.emailPlaceholder') ?>" value="<?= old('email') ?>" required autofocus>
                    <?php if (session('errors.email')) : ?>
                        <div class="invalid-feedback"><?= session('errors.email') ?></div>
                    <?php endif ?>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label"><?= lang('Auth.password') ?></label>
                    <div class="password-toggle">
                        <input type="password" class="form-control <?php if (session('errors.password')) : ?>is-invalid<?php endif ?>" id="password" name="password" placeholder="<?= lang('Auth.passwordPlaceholder') ?>" required>
                        <i class="bi bi-eye toggle-icon" id="togglePassword"></i>
                    </div>
                    <?php if (session('errors.password')) : ?>
                        <div class="text-danger small mt-1"><?= session('errors.password') ?></div>
                    <?php endif ?>
                </div>

                <div class="remember-forgot">
                    <?php if (setting('Auth.sessionConfig')['allowRemembering']): ?>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" <?php if (old('remember')): ?> checked<?php endif ?>>
                        <label class="form-check-label" for="remember">
                            <?= lang('Auth.rememberMe') ?>
                        </label>
                    </div>
                    <?php endif; ?>
                    <?php if (setting('Auth.allowMagicLinkLogins')) : ?>
                        <a href="<?= lang_url('magic-link') ?>" class="text-decoration-none"><?= lang('Auth.forgotPassword') ?></a>
                    <?php endif ?>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <?= lang('Auth.loginButton') ?>
                </button>
            </form>

            <div class="divider">
                <span><?= lang('Auth.or') ?></span>
            </div>

            <div class="text-center">
                <?php if (siteconfig()->allowRegistration) : ?>
                    <p class="mb-0"><?= lang('Auth.noAccount') ?> <a href="<?= lang_url('register') ?>" class="text-decoration-none fw-semibold"><?= lang('Auth.registerNow') ?></a></p>
                <?php endif ?>
            </div>
        </div>
    </div>

    <!-- Right Side - Benefits -->
    <div class="benefits-side">
        <div class="benefits-content">
            <?php if (!empty($logoUrl)): ?>
                <div class="mb-4 text-center">
                    <img src="<?= esc($logoUrl) ?>" alt="<?= esc($siteName) ?>" style="height: <?= esc($logoHeight) ?>px; max-width: 100%; object-fit: contain;">
                </div>
            <?php endif; ?>
            <h2 class="benefits-title"><?= lang('Auth.benefitsTitle') ?></h2>
            <p class="benefits-subtitle"><?= lang('Auth.benefitsSubtitle') ?></p>

            <ul class="feature-list">
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?= lang('Auth.feature1') ?></span>
                </li>
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?= lang('Auth.feature2') ?></span>
                </li>
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?= lang('Auth.feature3') ?></span>
                </li>
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?= lang('Auth.feature4') ?></span>
                </li>
            </ul>

            <div class="mt-4">
                <small class="opacity-75">
                    <i class="bi bi-shield-check"></i> <?= lang('Auth.sslEncrypted') ?> •
                    <i class="bi bi-server"></i> <?= lang('Auth.secureServers') ?> •
                    <i class="bi bi-check-circle"></i> <?= lang('Auth.gdprCompliant') ?>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
    // Password Toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    }
</script>

</body>
</html>
