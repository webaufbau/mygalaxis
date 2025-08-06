<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Code eingeben</title>
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
    <div class="elementor-element elementor-element-67308e5 e-con-full e-flex e-con e-parent e-lazyloaded" data-id="67308e5" data-element_type="container" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
        <div class="elementor-element elementor-element-817a056 elementor-widget elementor-widget-image" data-id="817a056" data-element_type="widget" data-widget_type="image.default">
            <div class="elementor-widget-container">
                <a href="<?=$siteConfig->frontendUrl;?>">
                    <img src="<?=$siteConfig->logoUrl;?>" class="attachment-large size-large wp-image-1581" alt="-logo">
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


    <?php
    $phone = session('phone');
    $method = session('verify_method');
    ?>

    <h2>Bestätigungscode eingeben</h2>

    <?php if ($method === 'sms'): ?>
        <div id="sms-status-box" class="alert alert-info">
            Wir senden eine SMS mit Ihrem Bestätigungscode an <strong id="sms-number"><?= esc($phone) ?></strong>.
        </div>

        <script>
            const smsNumber = document.getElementById('sms-number').textContent;
            const statusBox = document.getElementById('sms-status-box');
            let attempts = 0;
            const maxAttempts = 5;
            let timerId;

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
                            updateStatusBox(`✅ SMS erfolgreich zugestellt an ${smsNumber}.`, 'success');
                            clearTimeout(timerId);
                        } else if (data.status === 'PENDING_ENROUTE' || data.status === 'PENDING_ACCEPTED') {
                            updateStatusBox(`⏳ SMS wird zugestellt... Bitte warten.`);
                            setTimeout(pollSmsStatus, 5000);
                        } else if (attempts >= maxAttempts) {
                            updateStatusBox(`❌ Der Status konnte leider nicht ermittelt werden, dies ist ein Hinweis, dass die Telefonnummer nicht korrekt sein könnte. Falls Sie keine SMS erhalten haben, überprüfen Sie bitte die eingegebene Telefonnummer und klicken Sie anschliessend auf „Telefonnummer anpassen“. <a href="/verification/confirm">Um einen neuen Code anzufordern klicken Sie hier.</a>`, 'danger');
                            clearTimeout(timerId);
                        } else if (data.status === 'NO_RESULT') {
                            updateStatusBox(`⏳ Status wird ermittelt... Bitte warten.`);
                            setTimeout(pollSmsStatus, 5000);
                        } else if (data.status === 'INVALID_DESTINATION_ADDRESS' || data.status === 'UNDELIVERABLE') {
                            updateStatusBox(`❌ SMS konnte nicht zugestellt werden. Bitte prüfen Sie die Nummer ${smsNumber}.`, 'danger');
                        } else if (data.status === 'ERROR' || data.status === 'NO_MESSAGE_ID') {
                            updateStatusBox(`❌ Fehler beim SMS-Versand: ${data.description || data.message}`, 'danger');
                        } else {
                            updateStatusBox(`ℹ️ Status: ${data.status}. Bitte warten...`);
                            setTimeout(pollSmsStatus, 5000);
                        }
                    })
                    .catch(() => {
                        updateStatusBox('⚠️ Verbindungsfehler beim Abrufen des SMS-Status. Versuche es erneut...', 'warning');
                        timerId = setTimeout(pollSmsStatus, 5000);
                    });
            }

            // Starte den Poll nach 5 Sekunden (nach Seitenladezeit)
            window.addEventListener('load', () => {
                timerId = setTimeout(pollSmsStatus, 5000);
            });
        </script>

    <?php else: ?>
        <p>Sie erhalten in wenigen Sekunden einen Anruf auf <strong><?= esc($phone) ?></strong> mit Ihrem Bestätigungscode.</p>
    <?php endif; ?>

    <form method="post" action="<?= site_url('/verification/verify') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="otp" class="form-label">Bestätigungscode</label>
            <div class="input-group justify-content-center">
                <div class="d-flex gap-2">
                    <input type="tel" maxlength="1" class="form-control form-control-lg text-center otp-input" style="width: 60px; font-size: 2rem;">
                    <input type="tel" maxlength="1" class="form-control form-control-lg text-center otp-input" style="width: 60px; font-size: 2rem;">
                    <input type="tel" maxlength="1" class="form-control form-control-lg text-center otp-input" style="width: 60px; font-size: 2rem;">
                    <input type="tel" maxlength="1" class="form-control form-control-lg text-center otp-input" style="width: 60px; font-size: 2rem;">
                </div>
                <input type="hidden" name="code" id="otp-code">
                <button type="submit" class="btn btn-success btn-lg ms-3 btn-submit-code" style="border-radius: 8px;" name="submitbutton" value="submitcode">Code bestätigen</button>
            </div>

        </div>


        <br><br><br>


        <div class="alert alert-light border">
            Sollte die angezeigte Telefonnummer nicht korrekt sein oder Sie keinen Code erhalten haben, geben Sie bitte Ihre richtige Telefonnummer unten ein und senden Sie das Formular erneut ab.<br>
            <strong>Hinweis:</strong> Zu häufige Anfragen hintereinander werden automatisch blockiert.
        </div>


        <div class="mb-3" style="max-width: 600px; margin: 0 auto;">
            <label for="phone" class="form-label">Telefonnummer</label>
            <div class="input-group">
                <input type="tel" name="phone" id="phone" class="form-control" value="<?= esc($phone) ?>">
                <button type="submit" class="btn btn-secondary" name="submitbutton" value="changephone">Telefonnummer anpassen</button>
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
