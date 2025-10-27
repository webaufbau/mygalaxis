<p><?= lang('Email.greeting', [$firma->contact_person]) ?></p>

<p><?= lang('Email.priceUpdateIntro') ?></p>

<div class="highlight" style="background-color: #f8f9fa; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0;">
    <h3 style="margin-top: 0; color: #ff9800;"><?= esc($offer['title'] ?? lang('Offers.type.' . $offer['type'])) ?></h3>
    <p><strong><?= lang('Offers.labels.zip') ?>:</strong> <?= esc($offer['zip']) ?> <?= esc($offer['city'] ?? '') ?></p>
    <p><strong><?= lang('Offers.labels.language') ?>:</strong> <?= esc($offer['language']) ?></p>
</div>

<h3><?= lang('Email.offer_details') ?></h3>

<?php if (isset($alreadyPurchased) && $alreadyPurchased): ?>
    <!-- Kontaktdaten Box wenn bereits gekauft -->
    <div style="background-color: #d4edda; border: 2px solid #28a745; border-radius: 8px; padding: 20px; margin: 20px 0;">
        <h4 style="color: #155724; margin-top: 0;"><?= lang('Email.customer_contact_details') ?></h4>
        <ul style="list-style: none; padding: 0;">
            <?php if (!empty($offer['data']['vorname']) || !empty($offer['data']['nachname'])): ?>
                <li><strong><?= lang('Email.Name') ?>:</strong> <?= esc($offer['data']['vorname'] ?? '') ?> <?= esc($offer['data']['nachname'] ?? '') ?></li>
            <?php endif; ?>
            <?php if (!empty($offer['data']['email'])): ?>
                <li><strong><?= lang('Email.Email') ?>:</strong> <a href="mailto:<?= esc($offer['data']['email']) ?>"><?= esc($offer['data']['email']) ?></a></li>
            <?php endif; ?>
            <?php if (!empty($offer['data']['phone']) || !empty($offer['data']['telefon']) || !empty($offer['data']['tel'])): ?>
                <?php $phone = $offer['data']['phone'] ?? $offer['data']['telefon'] ?? $offer['data']['tel'] ?? ''; ?>
                <li><strong><?= lang('Email.Phone') ?>:</strong> <a href="tel:<?= esc($phone) ?>"><?= esc($phone) ?></a></li>
            <?php endif; ?>
            <?php if (!empty($offer['data']['mobile']) || !empty($offer['data']['handy'])): ?>
                <?php $mobile = $offer['data']['mobile'] ?? $offer['data']['handy'] ?? ''; ?>
                <li><strong><?= lang('Email.mobile') ?>:</strong> <a href="tel:<?= esc($mobile) ?>"><?= esc($mobile) ?></a></li>
            <?php endif; ?>
            <?php if (!empty($offer['data']['strasse']) || !empty($offer['data']['street']) || !empty($offer['data']['address']) || !empty($offer['data']['adresse'])): ?>
                <?php
                $address = $offer['data']['strasse'] ?? $offer['data']['street'] ?? $offer['data']['address'] ?? $offer['data']['adresse'] ?? '';
                // Format address if it's an array
                if (is_array($address)) {
                    $address = implode(', ', array_filter($address, fn($v) => !empty($v)));
                }
                ?>
                <li><strong><?= lang('Email.address') ?>:</strong> <?= esc($address) ?></li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>

<ul>
    <?php
    // Lade die Sprachübersetzungen
    $labels = lang('Offers.labels');

    // Lade zentrale Konfiguration für Ausschlussfelder
    $fieldConfig = new \Config\FormFieldOptions();

    // Wenn bereits gekauft, zeige alle Felder (außer den Always-Excluded)
    // Wenn nicht gekauft, verstecke zusätzlich Kontaktdaten
    if (isset($alreadyPurchased) && $alreadyPurchased) {
        $excludedFields = $fieldConfig->excludedFieldsAlways;
    } else {
        $excludedFields = array_merge(
            $fieldConfig->excludedFieldsAlways,
            [
                'vorname', 'nachname', 'email', 'phone', 'telefon', 'tel',
                'e-mail', 'e_mail', 'mail', 'mobile', 'handy',
                'strasse', 'street', 'address', 'adresse', 'hausnummer'
            ] // Keine Kontaktdaten anzeigen wenn nicht gekauft
        );
    }

    foreach ($offer['data'] as $key => $value):
        // Normalisiere Key für Vergleich
        $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));

        // Skip ausgeschlossene Felder
        $shouldExclude = false;
        foreach ($excludedFields as $excludedField) {
            if (strtolower($excludedField) === $normalizedKey) {
                $shouldExclude = true;
                break;
            }
        }
        if ($shouldExclude) {
            continue;
        }

        // Filtere FluentForm Nonce Felder
        if (preg_match('/fluentform.*nonce/i', $key)) {
            continue;
        }

        // Filtere "names" Feld
        if ($normalizedKey === 'names') {
            continue;
        }

        // Übersetzung vorhanden?
        if (isset($labels[$key])) {
            $label = $labels[$key];
        } else {
            $label = ucwords(str_replace(['_', '-'], ' ', $key));
        }

        // Konvertiere bool-artige Strings
        $cleanValue = is_string($value) ? trim(strtolower($value)) : $value;

        // Skip if value is "nein", false, null, or empty
        if ($cleanValue === 'nein' || $cleanValue === false || $cleanValue === null || $cleanValue === '') {
            continue;
        }

        // Format Arrays oder JSON-Daten lesbar
        if (is_array($value)) {
            $display = implode(', ', array_map('esc', $value));
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $display = implode(', ', array_map('esc', array_filter($decoded, fn($v) => !in_array(strtolower((string)$v), ['nein', '', null], true))));
            } else {
                $display = esc($value);

                // Datum formatieren
                if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $value)) {
                    $timestamp = DateTime::createFromFormat('d/m/Y', $value);
                    if ($timestamp) {
                        $display = $timestamp->format('d.m.Y');
                    }
                } elseif (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
                    $timestamp = DateTime::createFromFormat('Y-m-d', $value);
                    if ($timestamp) {
                        $display = $timestamp->format('d.m.Y');
                    }
                }
            }
        } else {
            $display = esc($value);
        }
        ?>
        <li><strong><?= esc($label) ?>:</strong>
            <?php
            if (in_array($key, ['file-upload', 'file_upload', 'upload_file'])) {
                $urls = is_array($value) ? $value : [$value];
                foreach ($urls as $url) {
                    if (is_string($url) && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $url)) {
                        echo '<br><img src="' . esc($url) . '" alt="Upload" style="max-width: 400px; width: 100%; height: auto; border:1px solid #ccc; padding: 5px; display: block; margin: 10px 0;">';
                    } elseif (filter_var($url, FILTER_VALIDATE_URL)) {
                        echo '<br><a href="' . esc($url) . '" target="_blank">' . esc(basename($url)) . '</a>';
                    } else {
                        echo esc($url);
                    }
                }
            } else {
                echo $display;
            }
            ?>
        </li>
    <?php endforeach; ?>
</ul>

<!-- Preis-Änderung hervorheben -->
<div style="background-color: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; padding: 20px; margin: 30px 0;">
    <h3 style="margin-top: 0; color: #856404;"><?= lang('Email.priceUpdateIntro') ?></h3>

    <p style="margin: 10px 0;">
        <span style="text-decoration: line-through; color: #6c757d; font-size: 18px;">
            <?= lang('Email.oldPrice') ?>: <?= esc($oldPrice) ?> <?= currency($firma->platform ?? null) ?>
        </span>
    </p>

    <p style="margin: 10px 0;">
        <strong style="font-size: 24px; color: #28a745;">
            <?= lang('Email.newPrice') ?>: <?= esc($newPrice) ?> <?= currency($firma->platform ?? null) ?>
        </strong>
    </p>

    <p style="margin: 10px 0; color: #856404;">
        <strong>(<?= $discount ?>% <?= lang('Email.discountApplied') ?>)</strong>
    </p>

    <?php if (isset($alreadyPurchased) && $alreadyPurchased): ?>
        <!-- Bereits gekauft -->
        <div style="text-align: center; margin-top: 20px;">
            <p style="margin: 0; font-size: 18px; color: #155724; font-weight: bold;">
                ✓ <?= lang('Email.already_purchased') ?>
            </p>
        </div>
    <?php else: ?>
        <!-- Jetzt kaufen Button -->
        <div style="text-align: center; margin-top: 20px;">
            <a href="<?= rtrim($siteConfig->backendUrl, '/') . '/offers/buy/' . $offer['id'] ?>"
               style="display: inline-block; background-color: #28a745; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-size: 18px; font-weight: bold;">
                <?= lang('Email.buy_now') ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<p><?= lang('Email.successWishes') ?></p>
