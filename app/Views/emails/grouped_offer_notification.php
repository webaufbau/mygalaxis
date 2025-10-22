
<h2><?= $isMultiple ? lang('Email.offer_added_multiple_subject') : lang('Email.offer_added_email_subject') ?></h2>

<p><?= lang('Email.offer_added_greeting', [$data['vorname'] ?? $data['names'] ?? '', $data['nachname'] ?? '']) ?></p>

<div class="highlight">
    <?php if ($isMultiple): ?>
        <p><?= lang('Email.offer_added_multiple_thank_you', [count($offers), esc($siteConfig->name)]) ?></p>
        <p><?= lang('Email.offer_added_info_1') ?></p>
        <p><?= lang('Email.offer_added_info_2') ?></p>
    <?php else: ?>
        <p><?= lang('Email.offer_added_thank_you', ['service' => esc(lang('Offers.type.' . ($offers[0]['type'] ?? 'other')))]) ?></p>
        <p><?= lang('Email.offer_added_info_1') ?></p>
        <p><?= lang('Email.offer_added_info_2') ?></p>
    <?php endif; ?>
</div>

<h3><?= lang('Email.offer_added_how_it_works') ?></h3>
<ul>
    <li><?= lang('Email.offer_added_how_1') ?></li>
    <li><?= lang('Email.offer_added_how_2') ?></li>
    <li><?= lang('Email.offer_added_how_3') ?></li>
    <li><?= lang('Email.offer_added_how_4') ?></li>
</ul>

<p><strong><?= lang('Email.offer_added_how_note') ?></strong></p>

<?php if ($isMultiple): ?>
    <h3><?= lang('Email.offer_added_requests_overview') ?></h3>
<?php endif; ?>

<?php
// Lade die Sprachübersetzungen
$labels = lang('Offers.labels');

// Lade zentrale Konfiguration für Ausschlussfelder
$fieldConfig = new \Config\FormFieldOptions();
$excludedFields = array_merge(
    $fieldConfig->excludedFieldsAlways,
    ['vorname', 'nachname', 'names', 'email', 'phone'] // Diese werden nur einmal oben angezeigt
);

foreach ($offers as $index => $offer):
    $offerNumber = $index + 1;
    $typeName = lang('Offers.type.' . $offer['type']);
?>

    <?php if ($isMultiple): ?>
        <div style="border: 2px solid #007bff; padding: 20px; margin: 20px 0; border-radius: 8px; background-color: #f8f9fa;">
            <h4 style="color: #007bff; margin-top: 0;"><?= lang('Email.offer_added_request_number', [$offerNumber, esc($typeName)]) ?></h4>
    <?php else: ?>
        <h3><?= lang('Email.offer_added_summary') ?></h3>
    <?php endif; ?>

    <ul>
        <?php
        $missingTranslations = [];

        foreach ($offer['filteredFields'] as $key => $value):
            // Normalisiere Key für Vergleich
            $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));

            // Skip ausgeschlossene Felder
            if (in_array($normalizedKey, $excludedFields)) {
                continue;
            }

            // Übersetzung vorhanden?
            if (isset($labels[$key])) {
                $label = $labels[$key];
            } else {
                $label = ucwords(str_replace(['_', '-'], ' ', $key));
                $missingTranslations[$key] = $label;
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
                            echo '<br><img src="' . esc($url) . '" alt="Upload" style="max-width: 100%; height: auto; border:1px solid #ccc; padding: 5px;">';
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

    <?php if ($isMultiple): ?>
        </div>
    <?php endif; ?>

    <?php
    // Fehlende Übersetzungen speichern
    if (!empty($missingTranslations)) {
        $file = WRITEPATH . 'missing_translations.txt';
        $content = '';
        foreach ($missingTranslations as $key => $label) {
            $content .= "'".$key."'" . " => '" . $label . "'," . PHP_EOL;
        }
        file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
    }
    ?>

<?php endforeach; ?>
