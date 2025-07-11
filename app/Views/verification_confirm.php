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
    <?php
    $phone = session('phone');
    $method = session('verify_method');
    ?>

    <h2>Bestätigungscode eingeben</h2>

    <?php if ($method === 'sms'): ?>
        <p>Wir haben eine SMS mit Ihrem Bestätigungscode an <strong><?= esc($phone) ?></strong> gesendet.</p>
    <?php else: ?>
        <p>Sie erhalten in wenigen Sekunden einen Anruf auf <strong><?= esc($phone) ?></strong> mit Ihrem Bestätigungscode.</p>
    <?php endif; ?>

    <p>Falls das nicht Ihre Telefonnummer ist oder Sie keinen Code erhalten haben, geben Sie bitte Ihre Telefonnummer erneut ein. Achten Sie auf die korrekte Eingabe:</p>

    <form method="post" action="<?= site_url('/verification/verify') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="code" class="form-label">Bestätigungscode</label>
            <input type="text" name="code" id="code" class="form-control" required>
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
