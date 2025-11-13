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
    <title><?= lang('Auth.magicLinkTitle') ?> - <?= esc($siteName) ?></title>
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

        .info-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .info-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-card p {
            font-size: 1.05rem;
            line-height: 1.8;
            opacity: 0.95;
            margin: 0;
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

            .info-card {
                padding: 1.5rem;
            }
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .back-link:hover {
            color: var(--secondary-color);
            gap: 0.75rem;
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
</head>
<body>

<!-- Language Switcher -->
<?php
$locales = ['de' => 'Deutsch', 'en' => 'English', 'fr' => 'Français', 'it' => 'Italiano'];
$currentLocale = service('request')->getLocale();
?>
<div class="language-switcher">
    <select class="form-select form-select-sm" onchange="location = this.value;">
        <?php foreach ($locales as $code => $name): ?>
            <?php
            $url = $code === 'de' ? site_url('magic-link') : site_url($code . '/magic-link');
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

            <h1><?= lang('Auth.magicLinkTitle') ?></h1>
            <p class="subtitle"><?= lang('Auth.magicLinkSubtitleText') ?></p>

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

            <form action="<?= lang_url('magic-link') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label"><?= lang('Auth.email') ?></label>
                    <input type="email" class="form-control <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" id="email" name="email" placeholder="<?= lang('Auth.emailPlaceholder') ?>" value="<?= old('email', auth()->user()->email ?? '') ?>" required autofocus>
                    <?php if (session('errors.email')) : ?>
                        <div class="invalid-feedback"><?= session('errors.email') ?></div>
                    <?php endif ?>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-envelope"></i> <?= lang('Auth.sendMagicLink') ?>
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="<?= lang_url('login') ?>" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                    <span><?= lang('Auth.backToLoginLink') ?></span>
                </a>
            </div>
        </div>
    </div>

    <!-- Right Side - Info -->
    <div class="benefits-side">
        <div class="benefits-content">
            <?php if (!empty($logoUrl)): ?>
                <div class="mb-4 text-center">
                    <img src="<?= esc($logoUrl) ?>" alt="<?= esc($siteName) ?>" style="height: <?= esc($logoHeight) ?>px; max-width: 100%; object-fit: contain;">
                </div>
            <?php endif; ?>
            <h2 class="benefits-title"><?= lang('Auth.magicLinkBenefitsTitle') ?></h2>
            <p class="benefits-subtitle"><?= lang('Auth.magicLinkBenefitsSubtitle') ?></p>

            <div class="info-card">
                <h3><i class="bi bi-magic"></i> <?= lang('Auth.magicLinkHowItWorks') ?></h3>
                <p>
                    <?= lang('Auth.magicLinkStep1') ?><br>
                    <?= lang('Auth.magicLinkStep2') ?><br>
                    <?= lang('Auth.magicLinkStep3') ?>
                </p>
            </div>

            <ul class="feature-list">
                <li>
                    <i class="bi bi-shield-check"></i>
                    <span><?= lang('Auth.magicLinkSecure') ?></span>
                </li>
                <li>
                    <i class="bi bi-clock-history"></i>
                    <span><?= lang('Auth.magicLinkValid') ?></span>
                </li>
                <li>
                    <i class="bi bi-key"></i>
                    <span><?= lang('Auth.magicLinkNoPassword') ?></span>
                </li>
                <li>
                    <i class="bi bi-lightning"></i>
                    <span><?= lang('Auth.magicLinkFastAccess') ?></span>
                </li>
            </ul>

            <div class="mt-4">
                <small class="opacity-75">
                    <i class="bi bi-info-circle"></i> <?= lang('Auth.magicLinkInfo') ?>
                </small>
            </div>
        </div>
    </div>
</div>

</body>
</html>
