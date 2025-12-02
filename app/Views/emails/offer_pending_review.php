
<h2><?= lang('Email.offer_pending_review_subject') ?></h2>

<p><?= lang('Email.offer_added_greeting', [$data['vorname'] ?? '', $data['nachname'] ?? '']) ?></p>

<div class="highlight">
    <p><?= lang('Email.offer_pending_review_thank_you', ['service' => esc($formular_page ?? lang('Offers.type.' . ($formName ?? 'other')))]) ?></p>
    <p><?= lang('Email.offer_pending_review_info') ?></p>
    <p><?= lang('Email.offer_pending_review_next_steps') ?></p>
</div>

<p><strong><?= lang('Email.offer_pending_review_note') ?></strong></p>

<h3><?= lang('Email.offer_added_summary') ?></h3>
<ul>
    <?php
    // Lade die Sprachübersetzungen
    $labels = lang('Offers.labels');

    // Lade zentrale Konfiguration für Ausschlussfelder
    $fieldConfig = new \Config\FormFieldOptions();
    $excludedFields = $fieldConfig->excludedFieldsAlways;

    foreach ($filteredFields as $key => $value):
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
        if ($shouldExclude) continue;

        // Filtere FluentForm Nonce Felder
        if (preg_match('/fluentform.*nonce/i', $key)) continue;

        // Filtere "names" Feld
        if ($normalizedKey === 'names') continue;

        // Übersetzung
        $label = isset($labels[$key]) ? $labels[$key] : ucwords(str_replace(['_', '-'], ' ', $key));

        // Skip empty/nein values
        $cleanValue = is_string($value) ? trim(strtolower($value)) : $value;
        if ($cleanValue === 'nein' || $cleanValue === false || $cleanValue === null || $cleanValue === '') {
            continue;
        }

        // Format Arrays oder JSON-Daten
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
                    if ($timestamp) $display = $timestamp->format('d.m.Y');
                } elseif (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
                    $timestamp = DateTime::createFromFormat('Y-m-d', $value);
                    if ($timestamp) $display = $timestamp->format('d.m.Y');
                }
            }
        } else {
            $display = esc($value);
        }
        ?>
        <li><strong><?= esc($label) ?>:</strong> <?= $display ?></li>
    <?php endforeach; ?>
</ul>
