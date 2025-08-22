<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= lang('Verification.title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="1;url=<?=$next_url;?>" />

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
    <div class="alert alert-success">
        <?= lang('Verification.successMessage'); ?>
        <a href="<?= esc($next_url); ?>"><?= lang('Verification.continue'); ?></a>
    </div>
</div>
</body>
</html>
