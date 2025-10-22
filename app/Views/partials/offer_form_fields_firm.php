<?php
// Lade Labels aus Sprachdatei
$fieldLabels = lang('Offers.labels');

// Standardwerte für optionale Variablen
$full = $full ?? false;
$admin = $admin ?? false;

// Form Fields laden
$formFields = json_decode($offer['form_fields'] ?? '', true) ?? [];
if (!empty($full)) {
    // Wenn gekauft: auch combo fields hinzufügen
    $formFields += json_decode($offer['form_fields_combo'] ?? '', true) ?? [];
} else {
    // Wenn nicht gekauft: auch combo fields hinzufügen
    $formFields += json_decode($offer['form_fields_combo'] ?? '', true) ?? [];
}

// Lade zentrale Konfiguration für Ausschlussfelder
$fieldConfigForExclusion = new \Config\FormFieldOptions();

// Felder filtern
$formFields = array_filter($formFields, function ($key) use ($fieldConfigForExclusion, $full, $admin) {
    // Normalisiere Key für Vergleich (Leerzeichen und Bindestriche zu Unterstrichen, kleingeschrieben)
    $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));

    // Felder, die IMMER ausgeschlossen werden
    if (in_array($normalizedKey, $fieldConfigForExclusion->excludedFieldsAlways)) {
        return false;
    }

    // FluentForm Nonce ausschließen
    if (preg_match('/^_fluentform_\d+_fluentformnonce$/', $key)) {
        return false;
    }

    // Kontaktdaten nur ausschließen wenn NICHT gekauft UND NICHT Admin
    if (empty($full) && empty($admin) && in_array($normalizedKey, $fieldConfigForExclusion->excludedFieldsBeforePurchase)) {
        return false;
    }

    // Adressfelder nur bei nicht-gekauften ausschließen
    if (empty($full) && empty($admin) && preg_match('/adresse|address/i', $key)) {
        return false;
    }

    return true;
}, ARRAY_FILTER_USE_KEY);

$fieldConfig = new \Config\FormFieldOptions();
$fieldsWithImages = $fieldConfig->fieldsWithImages;
$imageBaseUrl = $fieldConfig->imageBaseUrl;


?>

<?php if (!empty($formFields)): ?>
    <table class="table table-striped table-sm">
        <thead>
        </thead>
        <tbody>
        <?php foreach ($formFields as $key => $value): ?>
            <?php
            // Skip "nein", false, null, leere Strings
            $cleanValue = is_string($value) ? trim(strtolower($value)) : $value;
            if ($cleanValue === 'nein' || $cleanValue === false || $cleanValue === null || $cleanValue === '') continue;

            if ($key == 'phone') {
                $value = $offer['phone'] ?? $value;
            }

            // Label
            $label = $fieldLabels[$key] ?? ucwords(str_replace(['_', '-'], ' ', $key));

            // Ausgabe vorbereiten
            $display = '';

            if (is_array($value)) {
                $display = implode(', ', array_map('esc', $value));
            } elseif (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $display = implode(', ', array_map('esc', array_filter($decoded, fn($v) => !in_array(strtolower((string)$v), ['nein', '', null], true))));
                } else {
                    $display = esc($value);

                    // Datumsformatierung
                    if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $value)) {
                        $ts = DateTime::createFromFormat('d/m/Y', $value);
                        if ($ts) $display = $ts->format('d.m.Y');
                    } elseif (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
                        $ts = DateTime::createFromFormat('Y-m-d', $value);
                        if ($ts) $display = $ts->format('d.m.Y');
                    }
                }
            } else {
                $display = esc((string)$value);

                if (is_string($value)) {
                    if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $value)) {
                        $ts = DateTime::createFromFormat('d/m/Y', $value);
                        if ($ts) $display = $ts->format('d.m.Y');
                    } elseif (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
                        $ts = DateTime::createFromFormat('Y-m-d', $value);
                        if ($ts) $display = $ts->format('d.m.Y');
                    }
                }
            }
            ?>

            <tr>
                <td><?= esc($label) ?>
                    <?php
                    if (in_array($key, $fieldsWithImages)) {
                        $imageUrl = $imageBaseUrl . $key . '.jpg';
                        echo '<div><img src="' . esc($imageUrl) . '" alt="Bild für ' . esc($label) . '" style="max-width:100%; border:1px solid #ccc; margin-top: 10px;"></div>';
                    }
                    ?>
                </td>
                <td>


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



                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p><em>Keine Angaben verfügbar.</em></p>
<?php endif; ?>

