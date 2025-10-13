<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= lang('Verification.confirmTitle'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .elementor-1384 .elementor-element.elementor-element-67308e5:not(.elementor-motion-effects-element-type-background), .elementor-1384 .elementor-element.elementor-element-67308e5 > .elementor-motion-effects-container > .elementor-motion-effects-layer {
            background-color: #955CE9;
        }
        .elementor-widget-container {
            text-align: center;
        }
    </style>
</head>
<body class="bg-light">

<div data-elementor-type="header" data-elementor-id="1384" class="elementor elementor-1384 elementor-location-header" data-elementor-post-type="elementor_library">
    <div class="elementor-element elementor-element-67308e5 e-con-full e-flex e-con e-parent e-lazyloaded" data-id="67308e5" data-element_type="container" style="background-color: <?=$siteConfig->headerBackgroundColor ?? '';?>;" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
        <div class="elementor-element elementor-element-817a056 elementor-widget elementor-widget-image" data-id="817a056" data-element_type="widget" data-widget_type="image.default">
            <div class="elementor-widget-container">
                <a href="<?=$siteConfig->frontendUrl;?>">
                    <img src="<?=$siteConfig->logoUrl;?>" class="attachment-large size-large wp-image-1581" alt="-logo" height="<?=$siteConfig->logoHeightPixel ?? '';?>">
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5">
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


    <?php
    $phone = session('phone');
    $method = session('verify_method') ?? 'sms'; // Fallback auf SMS
    ?>

    <h2><?= lang('Verification.enterCode'); ?></h2>

    <?php if ($method === 'sms'): ?>
        <div id="sms-status-box" class="alert alert-info">
            <?= str_replace('{phone}', esc($phone), lang('Verification.smsSending')); ?>
        </div>
        <span id="sms-number" style="display:none;"><?= esc($phone) ?></span>

        <script>
            const smsNumber = document.getElementById('sms-number').textContent;
            const statusBox = document.getElementById('sms-status-box');
            let attempts = 0;
            const maxAttempts = 5;
            let timerId;

            // Übersetzungen aus PHP in JS übertragen
            const i18n = {
                smsDelivered: "<?= lang('Verification.smsDelivered') ?>",
                smsPending: "<?= lang('Verification.smsPending') ?>",
                smsFailed: "<?= lang('Verification.smsFailed') ?>",
                smsNoResult: "<?= lang('Verification.smsNoResult') ?>",
                smsInvalidNumber: "<?= lang('Verification.smsInvalidNumber') ?>",
                smsError: "<?= lang('Verification.smsError') ?>",
                smsUnknown: "<?= lang('Verification.smsUnknown') ?>",
                smsConnectionError: "<?= lang('Verification.smsConnectionError') ?>"
            };

            function updateStatusBox(message, type = 'info') {
                statusBox.innerHTML = message;
                statusBox.className = ''; // Klassen zurücksetzen
                statusBox.classList.add('alert', `alert-${type}`);
            }

            function pollSmsStatus() {
                attempts++;
                fetch('/verification/sms-status')
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'DELIVERED_TO_HANDSET' || data.status === 'DELIVERED') {
                            updateStatusBox(i18n.smsDelivered.replace('{phone}', smsNumber), 'success');
                            clearTimeout(timerId);
                        } else if (data.status === 'PENDING_ENROUTE' || data.status === 'PENDING_ACCEPTED') {
                            updateStatusBox(i18n.smsPending, 'info');
                            setTimeout(pollSmsStatus, 5000);
                        } else if (attempts >= maxAttempts) {
                            updateStatusBox(i18n.smsFailed, 'danger');
                            clearTimeout(timerId);
                        } else if (data.status === 'NO_RESULT') {
                            updateStatusBox(i18n.smsNoResult, 'info');
                            setTimeout(pollSmsStatus, 5000);
                        } else if (data.status === 'INVALID_DESTINATION_ADDRESS' || data.status === 'UNDELIVERABLE') {
                            updateStatusBox(i18n.smsInvalidNumber.replace('{phone}', smsNumber), 'danger');
                        } else if (data.status === 'ERROR' || data.status === 'NO_MESSAGE_ID') {
                            updateStatusBox(i18n.smsError.replace('{error}', data.description || data.message), 'danger');
                        } else {
                            updateStatusBox(i18n.smsUnknown.replace('{status}', data.status), 'info');
                            setTimeout(pollSmsStatus, 5000);
                        }
                    })
                    .catch(() => {
                        updateStatusBox(i18n.smsConnectionError, 'warning');
                        timerId = setTimeout(pollSmsStatus, 5000);
                    });
            }

            // Starte den Poll nach 5 Sekunden (nach Seitenladezeit)
            window.addEventListener('load', () => {
                timerId = setTimeout(pollSmsStatus, 5000);
            });
        </script>

    <?php else: ?>
        <p><?= str_replace('{phone}', esc($phone), lang('Verification.callSending')); ?></p>
    <?php endif; ?>

    <?php
    $locale = getCurrentLocale();
    $prefix = ($locale === 'de') ? '' : '/' . $locale;
    ?>
    <form method="post" action="<?= site_url($prefix . '/verification/verify') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="otp-code" class="form-label"><?= lang('Verification.codeLabel'); ?></label>
            <div class="input-group justify-content-center">
                <div class="d-flex gap-2">
                    <input type="tel" maxlength="1" class="form-control form-control-lg text-center otp-input" style="width: 60px; font-size: 2rem;">
                    <input type="tel" maxlength="1" class="form-control form-control-lg text-center otp-input" style="width: 60px; font-size: 2rem;">
                    <input type="tel" maxlength="1" class="form-control form-control-lg text-center otp-input" style="width: 60px; font-size: 2rem;">
                    <input type="tel" maxlength="1" class="form-control form-control-lg text-center otp-input" style="width: 60px; font-size: 2rem;">
                </div>
                <input type="hidden" name="code" id="otp-code">
                <button type="submit" class="btn btn-success btn-lg ms-3 btn-submit-code" style="border-radius: 8px;" name="submitbutton" value="submitcode">    <?= lang('Verification.submitCode'); ?></button>
            </div>

        </div>


        <br><br><br>


        <div class="alert alert-light border">
            <?= lang('Verification.changePhoneNote'); ?><br>
            <strong><?= lang('Verification.note'); ?>:</strong> <?= lang('Verification.noteText'); ?>
        </div>


        <div class="mb-3" style="max-width: 600px; margin: 0 auto;">
            <label for="phone" class="form-label"><?= lang('Verification.phoneLabel'); ?></label>
            <div class="input-group">
                <input type="tel" name="phone" id="phone" class="form-control" value="<?= esc($phone) ?>">
                <button type="submit" class="btn btn-secondary" name="submitbutton" value="changephone"><?= lang('Verification.changePhone'); ?></button>
            </div>
        </div>

    </form>
</div>



<style>
    @media (max-width: 768px) {
        .btn-submit-code {
            margin-top: 15px;
        }
    }
</style>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function () {
        const $inputs = $(".otp-input");

        // Eingabe validieren und automatisch weiterspringen
        $inputs.on("input", function () {
            const val = this.value.replace(/\D/g, ''); // nur Zahlen
            this.value = val;

            if (val.length === 1) {
                $(this).next(".otp-input").focus();
            }
            updateHiddenField();
        });

        // Mit Backspace zurückspringen
        $inputs.on("keydown", function (e) {
            if (e.key === "Backspace" && !this.value) {
                $(this).prev(".otp-input").focus();
            }
        });

        // Copy & Paste des gesamten Codes
        $inputs.first().on("paste", function (e) {
            const paste = e.originalEvent.clipboardData.getData("text").replace(/\D/g, '');
            if (paste.length === $inputs.length) {
                $inputs.each(function (i) {
                    this.value = paste[i] || "";
                });
                updateHiddenField();
            }
        });

        function updateHiddenField() {
            $("#otp-code").val(
                $inputs.map(function () { return this.value; }).get().join("")
            );
        }

        // **Erstes Feld automatisch aktivieren**
        $inputs.first().focus();
    });
</script>


</body>
</html>
