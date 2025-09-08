<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Anfrage wird verarbeitet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .loader-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .message {
            margin-top: 1.5rem;
            font-size: 1.2rem;
            color: #343a40;
        }

        .elementor-1384 .elementor-element.elementor-element-67308e5:not(.elementor-motion-effects-element-type-background), .elementor-1384 .elementor-element.elementor-element-67308e5 > .elementor-motion-effects-container > .elementor-motion-effects-layer {
            background-color: #955CE9;
        }
        .elementor-widget-container {
            text-align: center;
        }
    </style>

</head>
<body>

<div data-elementor-type="header" data-elementor-id="1384" class="elementor elementor-1384 elementor-location-header" data-elementor-post-type="elementor_library">
    <div class="elementor-element elementor-element-67308e5 e-con-full e-flex e-con e-parent e-lazyloaded" data-id="67308e5" data-element_type="container" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
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
    <div class="loader-container">
        <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
        <div class="message">
            <?= lang('General.processingRequest') ?><br>
            <?= lang('General.pleaseWait') ?>
        </div>
    </div>
</div>

<?php $locale = getCurrentLocale(); ?>

<script>
    const locale = '<?= esc($locale) ?>';

    function buildUrl(path) {
        if (locale === 'de') {
            return '/' + path;
        }
        return '/' + locale + '/' + path;
    }

    async function checkSession() {
        try {
            const response = await fetch(buildUrl('verification/check-session'));
            const data = await response.json();

            if (data.status === 'ok') {
                window.location.href = buildUrl('verification');
            } else {
                setTimeout(checkSession, 2000);
            }
        } catch (e) {
            setTimeout(checkSession, 5000);
        }
    }

    checkSession();
</script>


</body>
</html>
