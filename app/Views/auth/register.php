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

// Firmen-ID / UID
if($siteConfig->companyUidCheck == 'ch') {
    $companyUidLink = 'https://www.zefix.ch/de/search/entity/welcome';
    $companyUidName = 'Zefix';
    $companyUidInputmask = 'CHE-999.999.999';
    $companyUidPlaceholder = 'CHE-123.456.789';
    $companyUidPattern = '^CHE-[0-9]{3}\.[0-9]{3}\.[0-9]{3}$';
    $companyUidInvalidFeedback = sprintf(lang('Auth.companyUidRequired'), $companyUidPlaceholder);
}
elseif($siteConfig->companyUidCheck == 'at') {
    $companyUidLink = 'https://justizonline.gv.at/jop/web/firmenbuchabfrage';
    $companyUidName = 'Firmenbuch';
    $companyUidInputmask = 'FN999999[a]';
    $companyUidPlaceholder = 'FN123456a';
    $companyUidPattern = 'FN[0-9]{1,6}[a-z]$';
    $companyUidInvalidFeedback = sprintf(lang('Auth.companyUidRequired'), $companyUidPlaceholder);
}
elseif($siteConfig->companyUidCheck == 'de') {
    $companyUidLink = 'https://www.unternehmensregister.de/de/suche';
    $companyUidName = 'Unternehmensregister';
    $companyUidInputmask = 'DEA****.***99999';
    $companyUidPlaceholder = 'DEXxxxx.HRB12345';
    $companyUidPattern = '^DE[A-Z0-9]{4,8}\.(HRB|HRA|GsR)[0-9]{1,5}$';
    $companyUidInvalidFeedback = sprintf(lang('Auth.companyUidRequired'), $companyUidPlaceholder);
}

// Telefonnummer
if($siteConfig->phoneCheck == 'ch') {
    $companyPhonePlaceholder = '+41 78 123 45 67';
    $companyPhoneInputmask = '+99 99 999 99 99';
    $companyPhonePattern = '^\+41\s\d{2}\s\d{3}\s\d{2}\s\d{2}$';
    $companyPhoneInvalidFeedback = sprintf(lang('Auth.companyPhoneRequired'), $companyPhonePlaceholder);
}
elseif($siteConfig->phoneCheck == 'at') {
    $companyPhonePlaceholder = '+43 660 1234567';
    $companyPhoneInputmask = '+43 999 9999999';
    $companyPhonePattern = '^\+43\s\d{1,3}\s\d{5,7}$';
    $companyPhoneInvalidFeedback = sprintf(lang('Auth.companyPhoneRequired'), $companyPhonePlaceholder);
}
elseif($siteConfig->phoneCheck == 'de') {
    $companyPhonePlaceholder = '+49 30 12345678';
    $companyPhoneInputmask = '+49 99999 9999999';
    $companyPhonePattern = '^\+49\s\d{1,5}\s\d{5,8}$';
    $companyPhoneInvalidFeedback = sprintf(lang('Auth.companyPhoneRequired'), $companyPhonePlaceholder);
}
?>
<!DOCTYPE html>
<html lang="<?= service('request')->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= lang('Auth.registerTitle') ?> - <?= esc($siteName) ?></title>
    <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/img/favicon.png') ?>"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.3.4/jquery.inputmask.bundle.min.js"></script>
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
            align-items: flex-start;
            justify-content: center;
            padding: 3rem 2rem;
            background: #ffffff;
            overflow-y: auto;
            max-height: 100vh;
        }

        .form-container {
            width: 100%;
            max-width: 800px;
            padding-bottom: 3rem;
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

        h4 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary {
            padding: 0.875rem 2rem;
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
                max-height: none;
            }
        }

        .alert {
            border-radius: 10px;
            border: none;
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

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 0.75rem;
            margin-top: 0.5rem;
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
            $url = $code === 'de' ? site_url('register') : site_url($code . '/register');
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

            <h1><?= lang('Auth.registerTitle') ?></h1>
            <p class="subtitle">Erstellen Sie Ihr Firmenkonto und erhalten Sie Zugang zu neuen Kundenanfragen</p>

            <form method="post" action="<?= lang_url('register') ?>" class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <!-- Honeypot Protection -->
                <div class="mb-2 d-none">
                    <label for="registerHP" class="form-label"><?= lang('Auth.registerhp') ?></label>
                    <input type="text" class="form-control" id="registerHP" name="registerhp" value="<?= old('registerhp') ?>">
                </div>

                <!-- Email & Password -->
                <div class="mb-3">
                    <label for="email" class="form-label"><?= lang('Auth.emailAddress') ?> *</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="firma@beispiel.ch" required autofocus>
                    <div class="invalid-feedback"><?= lang('Auth.emailRequired') ?></div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label"><?= lang('Auth.password') ?> *</label>
                        <input type="password" name="password" id="password" class="form-control" required minlength="6">
                        <div class="invalid-feedback"><?= lang('Auth.passwordMinLength') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirm" class="form-label"><?= lang('Auth.passwordConfirm') ?> *</label>
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control" required minlength="6">
                        <div class="invalid-feedback"><?= lang('Auth.passwordConfirmRequired') ?></div>
                    </div>
                </div>

                <hr class="my-4">
                <h4><?= lang('Auth.companyDataTitle') ?></h4>

                <!-- Categories -->
                <div class="mb-4">
                    <label class="form-label"><?= esc(lang('Filter.categories')) ?> *</label>
                    <div class="category-grid">
                        <?php
                        $categoryOptions = new \Config\CategoryOptions();
                        $types = $categoryOptions->categoryTypes;
                        foreach ($types as $type_id => $cat):
                            $id = 'cat_' . strtolower(str_replace([' ', '+'], ['_', 'plus'], $cat));
                            $checked = in_array($type_id, $user_filters['filter_categories'] ?? []) ? 'checked' : '';
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="filter_categories[]" value="<?= esc($type_id) ?>" id="<?= esc($id) ?>" <?= $checked ?>>
                                <label class="form-check-label" for="<?= esc($id) ?>"><?= lang('Offers.type.'.$type_id); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Company Name -->
                <div class="mb-3">
                    <label for="company_name" class="form-label"><?= lang('Auth.companyName') ?> *</label>
                    <input type="text" name="company_name" id="company_name" class="form-control" required>
                    <div class="invalid-feedback"><?= lang('Auth.companyNameRequired') ?></div>
                </div>

                <!-- Contact Person -->
                <div class="mb-3">
                    <label for="contact_person" class="form-label"><?= esc(lang('Profile.contactPerson')) ?> *</label>
                    <input type="text" name="contact_person" id="contact_person" class="form-control" required>
                    <div class="invalid-feedback"><?= lang('Auth.contactPersonRequired') ?></div>
                </div>

                <!-- Company UID -->
                <div class="mb-3">
                    <label for="company_uid" class="form-label">
                        <?= lang('Auth.companyUid') ?> *
                        <?php if($siteConfig->companyUidCheck !== '') { echo '<a href="'.$companyUidLink.'" target="_blank" class="text-decoration-none">'.$companyUidName.' <i class="bi bi-box-arrow-up-right small"></i></a>'; } ?>
                    </label>
                    <input type="text" name="company_uid" id="company_uid" class="form-control"
                        <?php if(isset($companyUidPattern)) { echo 'pattern="'.$companyUidPattern.'"'; } ?>
                        <?php if(isset($companyUidPlaceholder)) { echo 'placeholder="'.$companyUidPlaceholder.'"'; } ?>
                        <?php if(isset($companyUidInvalidFeedback)) { echo 'title="'.$companyUidInvalidFeedback.'"'; } ?>>
                    <div class="invalid-feedback"><?= $companyUidInvalidFeedback ?? '' ?></div>
                </div>

                <!-- Street -->
                <div class="mb-3">
                    <label for="company_street" class="form-label"><?= lang('Auth.companyStreet') ?> *</label>
                    <input type="text" name="company_street" id="company_street" class="form-control" required>
                    <div class="invalid-feedback"><?= lang('Auth.companyStreetRequired') ?></div>
                </div>

                <!-- ZIP & City -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="company_zip" class="form-label"><?= lang('Auth.companyZip') ?> *</label>
                        <input type="text" name="company_zip" id="company_zip" class="form-control" required>
                        <div class="invalid-feedback"><?= lang('Auth.companyZipRequired') ?></div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="company_city" class="form-label"><?= lang('Auth.companyCity') ?> *</label>
                        <input type="text" name="company_city" id="company_city" class="form-control" required>
                        <div class="invalid-feedback"><?= lang('Auth.companyCityRequired') ?></div>
                    </div>
                </div>

                <!-- Phone -->
                <div class="mb-3">
                    <label for="company_phone" class="form-label"><?= lang('Auth.companyPhone') ?> *</label>
                    <input type="tel" name="company_phone" id="company_phone" class="form-control" required
                        <?php if(isset($companyPhonePattern)) { echo 'pattern="'.$companyPhonePattern.'"'; } ?>
                        <?php if(isset($companyPhonePlaceholder)) { echo 'placeholder="'.$companyPhonePlaceholder.'"'; } ?>
                        <?php if(isset($companyPhoneInvalidFeedback)) { echo 'title="'.$companyPhoneInvalidFeedback.'"'; } ?>>
                    <div class="invalid-feedback"><?= $companyPhoneInvalidFeedback ?? ''; ?></div>
                </div>

                <!-- Website -->
                <div class="mb-3">
                    <label for="company_website" class="form-label"><?= lang('Auth.companyWebsite') ?></label>
                    <input type="text" name="company_website" id="company_website" class="form-control" placeholder="https://www.beispiel.ch">
                    <div class="invalid-feedback"><?= lang('Auth.companyWebsiteRequired') ?></div>
                </div>

                <!-- AGB -->
                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="accept_agb" id="accept_agb" class="form-check-input" value="1" required>
                        <label for="accept_agb" class="form-check-label"><?= lang('General.acceptAGB') ?> *</label>
                        <div class="invalid-feedback"><?= lang('Auth.acceptAGBRequired') ?></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <?= lang('Auth.registerButton') ?>
                </button>
            </form>

            <div class="divider">
                <span><?= lang('Auth.or') ?></span>
            </div>

            <div class="text-center">
                <p class="mb-0"><?= lang('Auth.haveAccount') ?> <a href="<?= lang_url('login') ?>" class="text-decoration-none fw-semibold"><?= lang('Auth.login') ?></a></p>
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
$(document).ready(function() {
    <?php if(isset($companyUidInputmask) && $companyUidInputmask !== '') { ?>
    $('#company_uid').inputmask({
        mask: "<?=$companyUidInputmask;?>",
        definitions: {
            'A': { validator: "[A-Z]" },
            '9': { validator: "[0-9]" },
            '*': { validator: "[A-Za-z0-9.]"}
        },
        placeholder: "_",
        showMaskOnHover: false,
        showMaskOnFocus: true,
        clearIncomplete: true
    });
    <?php } ?>

    <?php if(isset($companyPhoneInputmask) && $companyPhoneInputmask !== '') { ?>
    $('#company_phone').inputmask({
        mask: "<?=$companyPhoneInputmask;?>",
        definitions: {
            'A': { validator: "[A-Z]" },
            '9': { validator: "[0-9]" },
            '*': { validator: "[A-Z0-9.]"}
        },
        placeholder: "_",
        showMaskOnHover: false,
        showMaskOnFocus: true,
        clearIncomplete: true
    });
    <?php } ?>

    // Bootstrap Validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                // Password matching
                var pwd = form.querySelector('#password');
                var pwdConfirm = form.querySelector('#password_confirm');
                if (pwd.value !== pwdConfirm.value) {
                    pwdConfirm.setCustomValidity("<?= lang('Auth.passwordsMismatch') ?>");
                } else {
                    pwdConfirm.setCustomValidity("");
                }

                // Check at least one category
                var categoriesChecked = form.querySelectorAll('input[name="filter_categories[]"]:checked').length;
                if (categoriesChecked === 0) {
                    event.preventDefault();
                    event.stopPropagation();

                    var categoryGroup = form.querySelector('.mb-4');
                    var feedback = categoryGroup.querySelector('.invalid-feedback');
                    if (!feedback) {
                        feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback d-block';
                        feedback.innerText = "<?= lang('Auth.selectAtLeastOneCategory') ?>";
                        categoryGroup.appendChild(feedback);
                    }
                    categoryGroup.classList.add('was-validated');
                    return false;
                }

                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
    })()
});
</script>

</body>
</html>
