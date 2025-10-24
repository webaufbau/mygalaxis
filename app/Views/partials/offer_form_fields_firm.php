<?php
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

// Bestimme welche Felder ausgeschlossen werden sollen
$excludedFields = $fieldConfigForExclusion->excludedFieldsAlways;

// FluentForm Nonce ausschließen (dynamisch)
$formFields = array_filter($formFields, function ($key) use ($excludedFields) {
    // FluentForm Nonce ausschließen
    if (preg_match('/^_fluentform_\d+_fluentformnonce$/', $key)) {
        return false;
    }
    return true;
}, ARRAY_FILTER_USE_KEY);

// Kontaktdaten nur ausschließen wenn NICHT gekauft UND NICHT Admin
if (empty($full) && empty($admin)) {
    $excludedFields = array_merge($excludedFields, $fieldConfigForExclusion->excludedFieldsBeforePurchase);

    // Adressfelder bei nicht-gekauften ausschließen
    $formFields = array_filter($formFields, function ($key) {
        return !preg_match('/adresse|address/i', $key);
    }, ARRAY_FILTER_USE_KEY);
}

// Versuche Email Template zu laden (mit field_display_template)
$renderedFields = null;
$useCustomTemplate = false;

if (!empty($offer['type'])) {
    $emailTemplateModel = new \App\Models\EmailTemplateModel();

    // Detect subtype from form fields
    $offerModel = new \App\Models\OfferModel();
    $subtype = $offerModel->detectSubtype($formFields);

    $template = $emailTemplateModel->getTemplateForOffer($offer['type'], 'de', $subtype);

    if ($template && !empty($template['field_display_template'])) {
        // Verwende das field_display_template aus dem Email Template
        $useCustomTemplate = true;

        // Parse das Template mit den Formulardaten
        $parser = new \App\Services\EmailTemplateParser();
        $parsedHtml = $parser->parse($template['field_display_template'], $formFields, $excludedFields);

        // Konvertiere HTML zurück zu Array-Format für die Tabellen-Darstellung
        // Für jetzt: einfach HTML direkt ausgeben
        $renderedHtmlOutput = $parsedHtml;
    }
}

// Fallback: Verwende FieldRenderer (alte Methode)
if (!$useCustomTemplate) {
    $fieldRenderer = new \App\Services\FieldRenderer();
    $fieldRenderer->setData($formFields)
                  ->setExcludedFields($excludedFields);

    // Rendere alle Felder (mit conditional groups)
    $renderedFields = $fieldRenderer->renderFields('html');
}
?>

<?php if ($useCustomTemplate && !empty($renderedHtmlOutput)): ?>
    <!-- Custom Template Output (direktes HTML aus field_display_template) -->
    <?= $renderedHtmlOutput ?>

<?php elseif (!empty($renderedFields)): ?>
    <!-- Fallback: Alte Tabellen-Darstellung mit FieldRenderer -->
    <table class="table table-striped table-sm">
        <tbody>
        <?php foreach ($renderedFields as $field): ?>
            <tr>
                <td>
                    <?= esc($field['label']) ?>

                    <?php if ($field['image']): ?>
                        <div>
                            <img src="<?= esc($field['image']) ?>"
                                 alt="Bild für <?= esc($field['label']) ?>"
                                 style="max-width:100%; border:1px solid #ccc; margin-top: 10px;">
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    // Initialisiere FieldRenderer für File-Upload Check (falls nicht bereits gesetzt)
                    if (!isset($fieldRenderer)) {
                        $fieldRenderer = new \App\Services\FieldRenderer();
                        $fieldRenderer->setData($formFields)->setExcludedFields($excludedFields);
                    }

                    // Spezialbehandlung für File-Uploads
                    if ($fieldRenderer->isFileUploadField($field['key'])) {
                        echo $fieldRenderer->formatFileUpload($field['value']);
                    } else {
                        echo esc($field['display']);
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
