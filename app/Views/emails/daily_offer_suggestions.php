<p><?= lang('Email.greeting', [$firma->contact_person]) ?></p>

<p><?= lang('Email.intro') ?></p>

<?php foreach ($offers as $offer): ?>
    <?php
    $alreadyPurchased = $offer['alreadyPurchased'] ?? false;
    $currentPrice = $offer['discounted_price'] ?? $offer['price'];
    ?>

    <div style="border: 2px solid #007bff; padding: 20px; margin-bottom: 30px; border-radius: 8px; background-color: #f8f9fa;">
        <h3 style="margin-top: 0; color: #007bff;"><?= esc($offer['title'] ?? lang('Offers.type.' . $offer['type'])) ?></h3>

        <p>
            <strong><?= lang('Offers.labels.zip') ?>:</strong> <?= esc($offer['zip']) ?> <?= esc($offer['city'] ?? '') ?><br>
            <strong><?= lang('Offers.labels.language') ?>:</strong> <?= esc($offer['language']) ?>
        </p>

        <?php if ($alreadyPurchased): ?>
            <!-- Kontaktdaten Box wenn bereits gekauft -->
            <div style="background-color: #d4edda; border: 2px solid #28a745; border-radius: 8px; padding: 15px; margin: 15px 0;">
                <h4 style="color: #155724; margin-top: 0; font-size: 16px;"><?= lang('Email.customer_contact_details') ?></h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
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
                </ul>
            </div>
        <?php endif; ?>

        <!-- Details der Anfrage (kompakt) -->
        <h4 style="margin: 15px 0 10px 0;"><?= lang('Email.offer_details') ?></h4>
        <ul style="margin: 0; padding-left: 20px;">
            <?php
            // Lade die Sprachübersetzungen
            $labels = lang('Offers.labels');
            $fieldConfig = new \Config\FormFieldOptions();

            if ($alreadyPurchased) {
                $excludedFields = $fieldConfig->excludedFieldsAlways;
            } else {
                $excludedFields = array_merge(
                    $fieldConfig->excludedFieldsAlways,
                    ['vorname', 'nachname', 'email', 'phone', 'telefon', 'tel', 'e-mail', 'e_mail', 'mail', 'mobile', 'handy', 'strasse', 'street', 'address', 'adresse', 'hausnummer']
                );
            }

            $displayedCount = 0;
            $maxDisplay = 5; // Zeige nur die wichtigsten 5 Felder

            foreach ($offer['data'] as $key => $value):
                if ($displayedCount >= $maxDisplay) break;

                $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));

                $shouldExclude = false;
                foreach ($excludedFields as $excludedField) {
                    if (strtolower($excludedField) === $normalizedKey) {
                        $shouldExclude = true;
                        break;
                    }
                }
                if ($shouldExclude) continue;

                if (preg_match('/fluentform.*nonce/i', $key)) continue;
                if ($normalizedKey === 'names') continue;

                $label = $labels[$key] ?? ucwords(str_replace(['_', '-'], ' ', $key));
                $cleanValue = is_string($value) ? trim(strtolower($value)) : $value;

                if ($cleanValue === 'nein' || $cleanValue === false || $cleanValue === null || $cleanValue === '') continue;

                if (is_array($value)) {
                    // Spezialbehandlung für Adressfelder: nur PLZ und Stadt anzeigen wenn nicht gekauft
                    if (preg_match('/adresse|address/i', $key) && !$alreadyPurchased) {
                        // Bei Adressfeldern nur zip und city anzeigen
                        $addressParts = [];
                        if (!empty($value['zip'])) {
                            $addressParts[] = esc($value['zip']);
                        }
                        if (!empty($value['city'])) {
                            $addressParts[] = esc($value['city']);
                        }
                        $display = implode(' ', $addressParts);

                        // Skip wenn keine PLZ/Stadt vorhanden
                        if (empty($display)) {
                            continue;
                        }
                    } else {
                        $display = implode(', ', array_map('esc', $value));
                    }
                } else {
                    $display = esc($value);
                }

                echo '<li><strong>' . esc($label) . ':</strong> ' . $display . '</li>';
                $displayedCount++;
            endforeach;

            if ($displayedCount >= $maxDisplay) {
                echo '<li><em>... ' . lang('Email.and_more_details') . '</em></li>';
            }
            ?>
        </ul>

        <!-- Preis und Kaufoption -->
        <div style="background-color: <?= $alreadyPurchased ? '#d4edda' : '#fff3cd' ?>; border: 2px solid <?= $alreadyPurchased ? '#28a745' : '#ffc107' ?>; border-radius: 5px; padding: 15px; margin: 15px 0;">
            <?php if ($alreadyPurchased): ?>
                <p style="margin: 0; text-align: center; font-size: 16px; color: #155724; font-weight: bold;">
                    ✓ <?= lang('Email.already_purchased') ?>
                </p>
            <?php else: ?>
                <?php if ($currentPrice > 0): ?>
                    <?php if (isset($offer['discounted_price']) && $offer['discounted_price'] > 0 && $offer['discounted_price'] < $offer['price']): ?>
                        <p style="margin: 0 0 5px 0;">
                            <span style="text-decoration: line-through; color: #6c757d;">
                                <?= lang('Email.original_price') ?>: <?= number_format($offer['price'], 2) ?> <?= currency($firma->platform ?? null) ?>
                            </span>
                        </p>
                        <p style="margin: 0 0 10px 0;">
                            <strong style="font-size: 20px; color: #28a745;">
                                <?= number_format($currentPrice, 2) ?> <?= currency($firma->platform ?? null) ?>
                            </strong>
                        </p>
                    <?php else: ?>
                        <p style="margin: 0 0 10px 0;">
                            <strong style="font-size: 20px; color: #007bff;">
                                <?= number_format($currentPrice, 2) ?> <?= currency($firma->platform ?? null) ?>
                            </strong>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

                <div style="text-align: center;">
                    <a href="<?= rtrim($siteConfig->backendUrl, '/') . '/offers/buy/' . $offer['id'] ?>" style="display: inline-block; padding: 12px 30px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;">
                        <?= lang('Email.buy_now') ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<p><?= lang('Email.successWishes') ?></p>
