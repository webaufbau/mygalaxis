<?php
// Standardwerte für optionale Variablen
$full = $full ?? false;
$admin = $admin ?? false;
$wrapInCard = $wrapInCard ?? true; // Standardmäßig Card-Wrapper hinzufügen

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

    // Bei Adressfeldern: nur sensible Daten (Straße, Hausnummer) entfernen, PLZ/Stadt behalten
    foreach ($formFields as $key => $value) {
        if (preg_match('/adresse|address/i', $key) && is_array($value)) {
            // Entferne address_line_1 und address_line_2, aber behalte zip und city
            if (isset($formFields[$key]['address_line_1'])) {
                unset($formFields[$key]['address_line_1']);
            }
            if (isset($formFields[$key]['address_line_2'])) {
                unset($formFields[$key]['address_line_2']);
            }
        }
    }
}

// Versuche Email Template zu laden (mit field_display_template)
$renderedFields = null;
$useCustomTemplate = false;

if (!empty($offer['type'])) {
    $emailTemplateModel = new \App\Models\EmailTemplateModel();

    // Detect subtype from form fields
    $offerModel = new \App\Models\OfferModel();
    $subtype = $offerModel->detectSubtype($formFields);

    // Verwende die aktuelle Sprache des Benutzers
    $currentLocale = service('request')->getLocale();
    $template = $emailTemplateModel->getTemplateForOffer($offer['type'], $currentLocale, $subtype);

    if ($template && !empty($template['field_display_template'])) {
        // Verwende das field_display_template aus dem Email Template
        $useCustomTemplate = true;

        // Parse das Template mit den Formulardaten
        $parser = new \App\Services\EmailTemplateParser();
        $parsedHtml = $parser->parse($template['field_display_template'], $formFields, $excludedFields);

        // Übersetze Feldwerte (z.B. "Ja" -> "Yes", "Nein" -> "No")
        helper('email_translation');
        $parsedHtml = translate_email_field_values($parsedHtml, $currentLocale);

        // Wenn wrapInCard false ist, entferne die Card-Struktur aus dem Template
        if (!$wrapInCard) {
            // Entferne äußere Card-Struktur aber behalte den Inhalt
            $parsedHtml = preg_replace('/<div\s+class="card"[^>]*>\s*<div\s+class="card-header"[^>]*>.*?<\/div>\s*<div\s+class="card-body"[^>]*>/s', '', $parsedHtml);
            $parsedHtml = preg_replace('/<\/div>\s*<\/div>\s*$/s', '', $parsedHtml);
        }

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

<?php
// Admin oder Gekauft: Zeige immer Kontaktdaten ganz oben
if (!empty($admin) || !empty($full)):
    // Sammle Kontaktinformationen
    $contactKeys = [
        'vorname' => lang('General.firstName'),
        'firstname' => lang('General.firstName'),
        'first_name' => lang('General.firstName'),
        'nachname' => lang('General.lastName'),
        'lastname' => lang('General.lastName'),
        'last_name' => lang('General.lastName'),
        'surname' => lang('General.lastName'),
        'email' => lang('General.email'),
        'e_mail' => lang('General.email'),
        'email_address' => lang('General.email'),
        'mail' => lang('General.email'),
        'e_mail_adresse' => lang('General.email'),
        'telefon' => lang('General.phone'),
        'telefonnummer' => lang('General.phone'),
        'phone' => lang('General.phone'),
        'telephone' => lang('General.phone'),
        'phone_number' => lang('General.phone'),
        'tel' => lang('General.phone')
    ];

    $customerInfo = [];
    $addressInfo = [];

    // Sammle Kontaktdaten
    foreach ($formFields as $key => $value) {
        $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));
        if (isset($contactKeys[$normalizedKey]) && !empty($value)) {
            $label = $contactKeys[$normalizedKey];
            if (!isset($customerInfo[$label])) {
                $customerInfo[$label] = $value;
            }
        }
    }

    // Sammle Adressinformationen (verschachtelte Arrays und direkte Felder)
    $addressKeys = [
        'strasse' => lang('General.street'),
        'street' => lang('General.street'),
        'address_line_1' => lang('General.street'),
        'hausnummer' => lang('General.houseNumber'),
        'house_number' => lang('General.houseNumber'),
        'nummer' => lang('General.houseNumber'),
        'address_line_2' => lang('General.houseNumber'),
    ];

    foreach ($formFields as $key => $value) {
        // Prüfe verschachtelte Adressfelder (z.B. einzug_adresse oder auszug_adresse)
        if (is_array($value) && (strpos(strtolower($key), 'adresse') !== false || strpos(strtolower($key), 'address') !== false)) {
            foreach ($value as $subKey => $subValue) {
                $normalizedSubKey = str_replace([' ', '-'], '_', strtolower($subKey));
                if (isset($addressKeys[$normalizedSubKey]) && !empty($subValue)) {
                    $label = $addressKeys[$normalizedSubKey];
                    if (!isset($addressInfo[$label])) {
                        $addressInfo[$label] = $subValue;
                    }
                }
            }
        }

        // Prüfe direkte Adressfelder
        $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));
        if (isset($addressKeys[$normalizedKey]) && !empty($value) && !is_array($value)) {
            $label = $addressKeys[$normalizedKey];
            if (!isset($addressInfo[$label])) {
                $addressInfo[$label] = $value;
            }
        }
    }

    // Zeige Kundeninformationen nur wenn Admin (nicht bei Firmen, die haben ihre eigene Card in show.php)
    if (!empty($customerInfo) && !empty($admin)):
        $cardBorderClass = 'border-primary';
        $cardHeaderClass = 'bg-primary bg-opacity-10';
        $iconColorClass = 'text-primary';
?>
    <div class="card mb-4 <?= $cardBorderClass ?>">
        <div class="card-header <?= $cardHeaderClass ?>">
            <h4 class="mb-0"><i class="bi bi-person-circle <?= $iconColorClass ?>"></i> <?= esc(lang('General.customerInfo')) ?></h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?php foreach ($customerInfo as $label => $value): ?>
                        <p class="mb-2">
                            <strong><?= esc($label) ?>:</strong>
                            <?php if ($label === 'E-Mail'): ?>
                                <a href="mailto:<?= esc($value) ?>"><?= esc($value) ?></a>
                            <?php elseif ($label === 'Telefon'): ?>
                                <a href="tel:<?= esc($value) ?>"><?= esc($value) ?></a>
                            <?php else: ?>
                                <?= esc($value) ?>
                            <?php endif; ?>
                        </p>
                    <?php endforeach; ?>
                </div>
                <div class="col-md-6">
                    <?php if (!empty($addressInfo)): ?>
                        <p class="mb-2">
                            <?php foreach ($addressInfo as $label => $value): ?>
                                <strong><?= esc($label) ?>:</strong> <?= esc($value) ?><br>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($offer['zip']) || !empty($offer['city']) || !empty($offer['type'])): ?>
                        <p class="mb-2">
                            <?php if (!empty($offer['zip'])): ?>
                                <strong><?= esc(lang('General.zipCode')) ?>:</strong> <?= esc($offer['zip']) ?><br>
                            <?php endif; ?>
                            <?php if (!empty($offer['city'])): ?>
                                <strong><?= esc(lang('General.city')) ?>:</strong> <?= esc($offer['city']) ?><br>
                            <?php endif; ?>
                            <?php if (!empty($offer['type'])): ?>
                                <strong><?= esc(lang('General.category')) ?>:</strong> <?= esc(lang('Offers.type.' . $offer['type'])) ?><br>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($offer['purchased_at'])): ?>
                        <p class="text-muted mb-0">
                            <small><?= esc(lang('General.purchasedOn')) ?>: <?= date('d.m.Y - H:i', strtotime($offer['purchased_at'])) ?><?= !empty(lang('Offers.time_suffix')) ? ' ' . lang('Offers.time_suffix') : '' ?></small>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php
    endif;
endif;
?>

<?php if ($wrapInCard): ?>
<div class="card">
    <div class="card-header">
        <h4 class="mb-0"><?= esc(lang('General.details')) ?></h4>
    </div>
    <div class="card-body">
<?php endif; ?>

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

<?php if ($wrapInCard): ?>
    </div>
</div>
<?php endif; ?>
