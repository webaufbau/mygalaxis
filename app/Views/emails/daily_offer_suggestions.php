<p><?= lang('Email.greeting', [$firma->contact_person]) ?></p>

<p><?= lang('Email.intro') ?></p>

<?php foreach ($offers as $offer): ?>
    <?php
    $formFields = json_decode($offer['form_fields'], true) ?? [];
    $labels = lang('Offers.labels');

    // Lade zentrale Konfiguration für Ausschlussfelder
    $fieldConfig = new \Config\FormFieldOptions();
    $excludedFields = array_merge(
        $fieldConfig->excludedFieldsAlways,
        $fieldConfig->excludedFieldsBeforePurchase // Kontaktdaten erst nach Kauf sichtbar
    );

    $currentPrice = $offer['discounted_price'] ?? $offer['price'];
    ?>

    <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
        <h3 style="margin-top: 0;"><?= esc($offer['title']) ?></h3>
        <p>
            <?php
            $zipLabel = lang('Offers.labels.zip');
            $cityLabel = lang('Offers.labels.city');
            $typeLabel = lang('Offers.labels.type');
            $priceLabel = lang('Offers.labels.price');

            // Fallback wenn Übersetzung nicht gefunden
            if (str_starts_with($zipLabel, 'Offers.')) $zipLabel = 'PLZ';
            if (str_starts_with($cityLabel, 'Offers.')) $cityLabel = 'Ort';
            if (str_starts_with($typeLabel, 'Offers.')) $typeLabel = 'Kategorie';
            if (str_starts_with($priceLabel, 'Offers.')) $priceLabel = 'Preis';
            ?>
            <strong><?= $zipLabel ?>:</strong> <?= esc($offer['zip']) ?><br>
            <strong><?= $cityLabel ?>:</strong> <?= esc($offer['city']) ?><br>
            <strong><?= $typeLabel ?>:</strong> <?= lang('Offers.type.' . $offer['type'], [], $offer['type']) ?><br>
            <?php if ($currentPrice > 0): ?>
                <strong><?= $priceLabel ?>:</strong> <?= number_format($currentPrice, 2) ?> CHF<br>
            <?php endif; ?>
        </p>

        <h4><?= lang('Email.offer_added_summary') ?? 'Details' ?></h4>
        <ul style="list-style: none; padding-left: 0;">
            <?php foreach ($formFields as $key => $value): ?>
                <?php
                // Normalisiere Key für Vergleich (Leerzeichen und Bindestriche zu Unterstrichen)
                $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));

                // Skip ausgeschlossene Felder
                if (in_array($normalizedKey, $excludedFields)) {
                    continue;
                }

                // Übersetzung
                $label = $labels[$key] ?? ucwords(str_replace(['_', '-'], ' ', $key));

                // Werte bereinigen
                $cleanValue = is_string($value) ? trim(strtolower($value)) : $value;
                if ($cleanValue === 'nein' || $cleanValue === false || $cleanValue === null || $cleanValue === '') {
                    continue;
                }

                // Werte formatieren
                if (is_array($value)) {
                    $display = implode(', ', array_map('esc', $value));
                } else {
                    $display = esc($value);
                }
                ?>
                <li><strong><?= esc($label) ?>:</strong> <?= $display ?></li>
            <?php endforeach; ?>
        </ul>

        <p>
            <a href="<?= rtrim($siteConfig->backendUrl, '/') . '/offers/' . $offer['id'] ?>" style="display: inline-block; padding: 10px 20px; background-color: #955CE9; color: white; text-decoration: none; border-radius: 5px;">
                <?= lang('Email.viewNow') ?>
            </a>
        </p>
    </div>
<?php endforeach; ?>

<p><?= lang('Email.successWishes') ?></p>

<p><?= lang('Email.greetings', [$siteConfig->name]) ?></p>
