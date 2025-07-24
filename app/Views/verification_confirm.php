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
                <a href="https://offertenschweiz.ch/">
                    <img src="https://offertenschweiz.ch/wp-content/uploads/2025/06/OFFERTENSchweiz00001.ch_.png" class="attachment-large size-large wp-image-1581" alt="offertenschweiz-logo" srcset="https://offertenschweiz.ch/wp-content/uploads/2025/06/OFFERTENSchweiz00001.ch_.png 1005w, https://offertenschweiz.ch/wp-content/uploads/2025/06/OFFERTENSchweiz00001.ch_-300x31.png 300w, https://offertenschweiz.ch/wp-content/uploads/2025/06/OFFERTENSchweiz00001.ch_-768x79.png 768w" sizes="(max-width: 800px) 100vw, 800px">								</a>
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
                statusBox.textContent = message;
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
                            updateStatusBox(`❌ Der Status konnte leider nicht ermittelt werden, dies ist ein Hinweis, dass die Telefonnummer nicht korrekt sein könnte. Falls Sie keine SMS erhalten haben, überprüfen Sie bitte die eingegebene Telefonnummer und klicken Sie anschliessend auf „Bestätigen“, um einen neuen Code anzufordern.`, 'danger');
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

    <p>
        Sollte die angezeigte Telefonnummer nicht korrekt sein oder Sie keinen Code erhalten haben, geben Sie bitte Ihre richtige Telefonnummer in das untenstehende Feld ein, lassen Sie das Feld für den Bestätigungscode leer und senden Sie das Formular erneut ab.
        So wird Ihnen ein neuer Code an die korrekte Nummer gesendet.
    </p>

    <form method="post" action="<?= site_url('/verification/verify') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="code" class="form-label">Bestätigungscode</label>
            <input type="text" name="code" id="code" class="form-control">
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Telefonnummer</label>
            <input type="tel" name="phone" id="phone" class="form-control" value="<?= esc($phone) ?>">
        </div>

        <button type="submit" class="btn btn-success">Bestätigen</button>
    </form>
</div>

</body>
</html>
