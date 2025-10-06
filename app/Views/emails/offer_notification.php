
<h2><?= lang('Email.offer_added_email_subject') ?></h2>

<p><?= lang('Email.offer_added_greeting', [$data['vorname'], $data['nachname']]) ?></p>

    <div class="highlight">
        <p><?= lang('Email.offer_added_thank_you', ['service' => esc($formular_page ?? lang('Offers.no_service'))]) ?></p>
        <p><?= lang('Email.offer_added_info_1') ?></p>
        <p><?= lang('Email.offer_added_info_2') ?></p>
    </div>

    <h3><?= lang('Email.offer_added_how_it_works') ?></h3>
    <ul>
        <li><?= lang('Email.offer_added_how_1') ?></li>
        <li><?= lang('Email.offer_added_how_2') ?></li>
        <li><?= lang('Email.offer_added_how_3') ?></li>
    </ul>

    <p><strong><?= lang('Email.offer_added_note') ?></strong></p>

    <?php if (!empty($data['additional_service']) && ! $data['additional_service'] == 'Nein'): ?>
        <p><strong><?= lang('Email.offer_added_additional_services') ?></strong> <?= esc($data['additional_service']) ?></p>
    <?php endif; ?>

    <h3><?= lang('Email.offer_added_summary') ?></h3>
    <ul>
        <?php
        // Lade die Sprachübersetzungen
        $labels = lang('Offers.labels');

        $missingTranslations = [];

        // Felder, die nicht angezeigt werden sollen
        $excludedFields = [
            'terms_n_condition',
            'terms_and_conditions',
            'terms',
            'type',
            'lang',
            'language',
            'csrf_test_name',
            'submit',
            'form_token',
        ];

        foreach ($filteredFields as $key => $value):
            // Skip ausgeschlossene Felder
            if (in_array(strtolower($key), $excludedFields)) {
                continue;
            }

            // Übersetzung vorhanden?
            if (isset($labels[$key])) {
                $label = $labels[$key];
            } else {
                $label = ucwords(str_replace(['_', '-'], ' ', $key));
                $missingTranslations[$key] = $label; // merken
            }

            //echo $key.'|';


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
                    // Formatierung für einzelne Werte
                    $display = esc($value);

                    // Datum erkennen: einfache Prüfung auf dd/mm/YYYY oder YYYY-mm-dd
                    if (is_string($value)) {
                        // Erkenne Datum im Format dd/mm/YYYY
                        if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $value)) {
                            $timestamp = DateTime::createFromFormat('d/m/Y', $value);
                            if ($timestamp) {
                                $display = $timestamp->format('d.m.Y');
                            }
                        }

                        // Erkenne Datum im Format YYYY-mm-dd
                        elseif (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
                            $timestamp = DateTime::createFromFormat('Y-m-d', $value);
                            if ($timestamp) {
                                $display = $timestamp->format('d.m.Y');
                            }
                        }
                    }
                }
            } else {
                // Formatierung für einzelne Werte
                $display = esc($value);

                // Datum erkennen: einfache Prüfung auf dd/mm/YYYY oder YYYY-mm-dd
                if (is_string($value)) {
                    // Erkenne Datum im Format dd/mm/YYYY
                    if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $value)) {
                        $timestamp = DateTime::createFromFormat('d/m/Y', $value);
                        if ($timestamp) {
                            $display = $timestamp->format('d.m.Y');
                        }
                    }

                    // Erkenne Datum im Format YYYY-mm-dd
                    elseif (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
                        $timestamp = DateTime::createFromFormat('Y-m-d', $value);
                        if ($timestamp) {
                            $display = $timestamp->format('d.m.Y');
                        }
                    }
                }

            }
            ?>
            <li><strong><?= esc($label) ?>:</strong>
                <?php
                if (in_array($key, ['file-upload', 'file_upload', 'upload_file'])) {
                    // Einzelner String oder Array?
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
                    echo esc($display);
                }
                ?>
            </li>


        <?php endforeach; ?>
    </ul>

<?php
// Fehlende Übersetzungen einmalig in Datei speichern
if (!empty($missingTranslations)) {
    $file = WRITEPATH . 'missing_translations.txt'; // z.B. writable/missing_translations.txt
    $content = '';
    foreach ($missingTranslations as $key => $label) {
        $content .= "'".$key."'" . " => '" . $label . "'," . PHP_EOL;
    }
    // Anhängen, ohne Datei zu überschreiben
    file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
}
