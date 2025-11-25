<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= $this->renderSection('title') ?> - <?= esc($title ?? siteconfig()->name) ?></title>

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

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <?= $this->renderSection('pageStyles') ?>

    <?php
    $siteConfig = siteconfig();
    if($siteConfig->faviconUrl !== '') {
    $mimeType = pathinfo($siteConfig->faviconUrl, PATHINFO_EXTENSION) === 'jpg' ? 'image/jpeg' : 'image/png';
    ?>
    <link rel="shortcut icon" type="<?= $mimeType ?>" href="<?= $siteConfig->faviconUrl ?>">
    <link rel="apple-touch-icon" href="<?= $siteConfig->faviconUrl ?>">
    <?php } ?>
</head>

<body class="bg-light">

    <main role="main" class="container">
        <?= $this->renderSection('main') ?>
    </main>

<?= $this->renderSection('pageScripts') ?>


    <footer class="bg-white border-top mt-5 py-3">
        <div class="container d-flex justify-content-center">
            <!-- Sprachumschalter -->
            <form method="get" action="" class="m-0">
                <?php
                $locales = ['de' => 'Deutsch', 'en' => 'English', 'fr' => 'Français', 'it' => 'Italiano'];
                $currentUri = service('uri')->getPath();
                $currentLocale = getCurrentLocale(array_keys($locales));
                ?>

                <select class="form-select form-select-sm" onchange="location = this.value;">
                    <?php foreach ($locales as $code => $name):
                        $url = base_url(changeLocaleInUri($currentUri, $code, array_keys($locales)));
                        $selected = ($code === $currentLocale) ? 'selected' : '';
                        ?>
                        <option value="<?= esc($url) ?>" <?= $selected ?>><?= esc($name) ?></option>
                    <?php endforeach; ?>
                </select>

            </form>
        </div>
    </footer>


</body>
</html>
