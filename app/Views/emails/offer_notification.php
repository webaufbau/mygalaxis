<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Deine Anfrage – Offerten folgen in Kürze</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background: #f6f6f6;
            padding: 20px;
            line-height: 1.5;
        }

        .container {
            background: #fff;
            padding: 30px;
            max-width: 700px;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }

        h1, h2, h3 {
            color: #0054a6;
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        li {
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #eee;
        }

        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #777;
            text-align: center;
        }

        .highlight {
            background: #e8f4ff;
            padding: 10px;
            border-left: 4px solid #0054a6;
            margin-bottom: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="container">

    <h1>🎉 Wir bestätigen Dir deine Anfrage/Offerte</h1>

    <p>Hallo <?= esc($data['vorname'] ?? ''); ?> <?= esc($data['nachname'] ?? ''); ?>,</p>

    <div class="highlight">
        <p>Herzlichen Dank für deine Anfrage für <strong><?= esc($formular_page ?? 'eine Dienstleistung') ?></strong>.</p>
        <p>In Kürze wirst du <strong>bis zu 3 unverbindliche Offerten</strong> von passenden Anbietern aus deiner Region erhalten. Je nach Saison kann es vorkommen, dass die Firmen für den gewünschten Zeitraum schon ausgebucht sind und daher keine Angebote unterbreiten.</p>
    </div>

    <h3>So funktioniert's:</h3>
    <ul>
        <li>Du erhältst Angebote per E-Mail – oft innerhalb von 1–2 Werktagen</li>
        <li>Anbieter können dich kontaktieren, falls Rückfragen bestehen</li>
        <li>Du entscheidest in Ruhe, welches Angebot am besten passt</li>
    </ul>

    <p><strong>Hinweis:</strong> Prüfe auch deinen Spam-/Werbungsordner, falls du keine E-Mail erhältst.</p>

    <?php if (!empty($data['additional_service']) && ! $data['additional_service'] == 'Nein'): ?>
        <p><strong>Du hast weitere Dienstleistungen angefragt:</strong> <?= esc($data['additional_service']) ?></p>
    <?php endif; ?>

    <h3>Zusammenfassung deiner Anfrage</h3>
    <ul>
        <?php
        // Lade die Sprachübersetzungen
        $labels = lang('Offers.labels');

        foreach ($filteredFields as $key => $value):
            // Übersetzung vorhanden?
            $label = $labels[$key] ?? ucwords(str_replace(['_', '-'], ' ', $key));

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

    <div class="footer">
        Diese Nachricht wurde automatisch generiert am <?= date('d.m.Y H:i') ?>.<br>
        Offerten Schweiz
    </div>
</div>
</body>
</html>
