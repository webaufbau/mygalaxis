<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Verifizierung anfordern</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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
                    <img width="800" height="83" src="https://offertenschweiz.ch/wp-content/uploads/2025/06/OFFERTENSchweiz00001.ch_.png" class="attachment-large size-large wp-image-1581" alt="offertenschweiz-logo" srcset="https://offertenschweiz.ch/wp-content/uploads/2025/06/OFFERTENSchweiz00001.ch_.png 1005w, https://offertenschweiz.ch/wp-content/uploads/2025/06/OFFERTENSchweiz00001.ch_-300x31.png 300w, https://offertenschweiz.ch/wp-content/uploads/2025/06/OFFERTENSchweiz00001.ch_-768x79.png 768w" sizes="(max-width: 800px) 100vw, 800px">								</a>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5">
    <h2>Telefonnummer bestätigen</h2>

    <p class="mb-4">Bitte bestätigen Sie Ihre Telefonnummer, um die Offerten sicher und korrekt zu erhalten. Ohne Bestätigung erhalten Sie keine Offerten.</p>

    <?php if (session('error')): ?>
        <div class="alert alert-danger"><?= session('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('/verification/send') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="phone" class="form-label">Telefonnummer</label>
            <input type="text" name="phone" class="form-control" value="<?= esc($phone) ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Verifizierungsmethode</label><br>

            <?php if ($isMobile): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="method" id="sms" value="sms" checked>
                    <label class="form-check-label" for="sms">SMS</label>
                </div>
            <?php endif; ?>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="method" id="call" value="call" <?= $isMobile ? '' : 'checked' ?>>
                <label class="form-check-label" for="call">Anruf</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Telefonnummer bestätigen</button>
    </form>
</div>

</body>
</html>
