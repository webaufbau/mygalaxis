<?= $this->extend('layout/minimal') ?>
<?= $this->section('content') ?>

<?php
// Übersetzungen
$translations = [
    'de' => [
        'termin' => 'Termin',
        'auftraggeber' => 'Auftraggeber',
        'kontakt' => 'Kontakt',
        'verify' => 'Bestätigung',
        'selected_services' => 'Gewählte Dienstleistungen',
        'when_start' => 'Wann sollen die Arbeiten beginnen?',
        'select_date' => 'Datum wählen',
        'time_flexible' => 'Bist du zeitlich flexibel?',
        'flex_no' => 'Nein',
        'flex_1_2_days' => 'Ja, 1 - 2 Tage',
        'flex_1_2_weeks' => 'Ja, 1 - 2 Wochen',
        'flex_1_month' => 'Ja, ca. 1 Monat',
        'flex_arrangement' => 'Nach Absprache',
        'client_type' => 'Auftraggeber(in)',
        'client_tenant' => 'Mieter',
        'client_owner' => 'Eigentümer',
        'client_business' => 'Geschäftlich',
        'client_public' => 'Öffentlich',
        'company_name' => 'Firmenname',
        'your_contact' => 'Ihre Kontaktdaten',
        'firstname' => 'Vorname',
        'lastname' => 'Nachname',
        'email' => 'E-Mail',
        'phone' => 'Telefon',
        'street' => 'Strasse',
        'house_number' => 'Hausnummer',
        'zip' => 'Postleitzahl',
        'city' => 'Ort',
        'phone_hint' => 'Bitte richtige Telefonnummer eingeben, wird geprüft!',
        'phone_reachable' => 'Wann bist du telefonisch erreichbar? (Nur für Rückfragen)',
        'reachable_allday' => 'ganztags',
        'reachable_08_10' => '08:00 - 10:00',
        'reachable_10_12' => '10:00 - 12:00',
        'reachable_12_14' => '12:00 - 14:00',
        'reachable_14_18' => '14:00 - 18:00',
        'reachable_18_20' => '18:00 - 20:00',
        'confirm_request' => 'Anfrage bestätigen',
        'confirm_info' => 'Bitte bestätige deine Anfrage. Du erhältst eine SMS oder E-Mail mit einem Bestätigungscode.',
        'summary' => 'Zusammenfassung',
        'services' => 'Dienstleistungen',
        'contact' => 'Kontakt',
        'address' => 'Adresse',
        'accept_terms' => 'Ich akzeptiere die',
        'terms' => 'AGB',
        'and' => 'und',
        'privacy' => 'Datenschutzbestimmungen',
        'back' => 'Zurück',
        'next' => 'Weiter',
        'submit' => 'Anfrage absenden',
    ],
    'en' => [
        'termin' => 'Schedule',
        'auftraggeber' => 'Client',
        'kontakt' => 'Contact',
        'verify' => 'Confirmation',
        'selected_services' => 'Selected services',
        'when_start' => 'When should the work begin?',
        'select_date' => 'Select date',
        'time_flexible' => 'Are you flexible with time?',
        'flex_no' => 'No',
        'flex_1_2_days' => 'Yes, 1 - 2 days',
        'flex_1_2_weeks' => 'Yes, 1 - 2 weeks',
        'flex_1_month' => 'Yes, about 1 month',
        'flex_arrangement' => 'By arrangement',
        'client_type' => 'Client type',
        'client_tenant' => 'Tenant',
        'client_owner' => 'Owner',
        'client_business' => 'Business',
        'client_public' => 'Public',
        'company_name' => 'Company name',
        'your_contact' => 'Your contact details',
        'firstname' => 'First name',
        'lastname' => 'Last name',
        'email' => 'Email',
        'phone' => 'Phone',
        'street' => 'Street',
        'house_number' => 'House number',
        'zip' => 'ZIP code',
        'city' => 'City',
        'phone_hint' => 'Please enter a valid phone number, it will be verified!',
        'phone_reachable' => 'When can we reach you by phone? (For questions only)',
        'reachable_allday' => 'all day',
        'reachable_08_10' => '08:00 - 10:00',
        'reachable_10_12' => '10:00 - 12:00',
        'reachable_12_14' => '12:00 - 14:00',
        'reachable_14_18' => '14:00 - 18:00',
        'reachable_18_20' => '18:00 - 20:00',
        'confirm_request' => 'Confirm request',
        'confirm_info' => 'Please confirm your request. You will receive an SMS or email with a confirmation code.',
        'summary' => 'Summary',
        'services' => 'Services',
        'contact' => 'Contact',
        'address' => 'Address',
        'accept_terms' => 'I accept the',
        'terms' => 'Terms',
        'and' => 'and',
        'privacy' => 'Privacy Policy',
        'back' => 'Back',
        'next' => 'Next',
        'submit' => 'Submit request',
    ],
    'fr' => [
        'termin' => 'Date',
        'auftraggeber' => 'Client',
        'kontakt' => 'Contact',
        'verify' => 'Confirmation',
        'selected_services' => 'Services sélectionnés',
        'when_start' => 'Quand les travaux doivent-ils commencer?',
        'select_date' => 'Choisir une date',
        'time_flexible' => 'Êtes-vous flexible sur le temps?',
        'flex_no' => 'Non',
        'flex_1_2_days' => 'Oui, 1 - 2 jours',
        'flex_1_2_weeks' => 'Oui, 1 - 2 semaines',
        'flex_1_month' => 'Oui, environ 1 mois',
        'flex_arrangement' => 'À convenir',
        'client_type' => 'Type de client',
        'client_tenant' => 'Locataire',
        'client_owner' => 'Propriétaire',
        'client_business' => 'Entreprise',
        'client_public' => 'Public',
        'company_name' => 'Nom de l\'entreprise',
        'your_contact' => 'Vos coordonnées',
        'firstname' => 'Prénom',
        'lastname' => 'Nom',
        'email' => 'E-mail',
        'phone' => 'Téléphone',
        'street' => 'Rue',
        'house_number' => 'Numéro',
        'zip' => 'Code postal',
        'city' => 'Ville',
        'phone_hint' => 'Veuillez entrer un numéro de téléphone valide, il sera vérifié!',
        'phone_reachable' => 'Quand pouvons-nous vous joindre par téléphone? (Uniquement pour des questions)',
        'reachable_allday' => 'toute la journée',
        'reachable_08_10' => '08:00 - 10:00',
        'reachable_10_12' => '10:00 - 12:00',
        'reachable_12_14' => '12:00 - 14:00',
        'reachable_14_18' => '14:00 - 18:00',
        'reachable_18_20' => '18:00 - 20:00',
        'confirm_request' => 'Confirmer la demande',
        'confirm_info' => 'Veuillez confirmer votre demande. Vous recevrez un SMS ou un e-mail avec un code de confirmation.',
        'summary' => 'Résumé',
        'services' => 'Services',
        'contact' => 'Contact',
        'address' => 'Adresse',
        'accept_terms' => 'J\'accepte les',
        'terms' => 'CGV',
        'and' => 'et',
        'privacy' => 'Politique de confidentialité',
        'back' => 'Retour',
        'next' => 'Suivant',
        'submit' => 'Envoyer la demande',
    ],
    'it' => [
        'termin' => 'Data',
        'auftraggeber' => 'Cliente',
        'kontakt' => 'Contatto',
        'verify' => 'Conferma',
        'selected_services' => 'Servizi selezionati',
        'when_start' => 'Quando devono iniziare i lavori?',
        'select_date' => 'Seleziona data',
        'time_flexible' => 'Sei flessibile con i tempi?',
        'flex_no' => 'No',
        'flex_1_2_days' => 'Sì, 1 - 2 giorni',
        'flex_1_2_weeks' => 'Sì, 1 - 2 settimane',
        'flex_1_month' => 'Sì, circa 1 mese',
        'flex_arrangement' => 'Da concordare',
        'client_type' => 'Tipo di cliente',
        'client_tenant' => 'Inquilino',
        'client_owner' => 'Proprietario',
        'client_business' => 'Aziendale',
        'client_public' => 'Pubblico',
        'company_name' => 'Nome azienda',
        'your_contact' => 'I tuoi dati di contatto',
        'firstname' => 'Nome',
        'lastname' => 'Cognome',
        'email' => 'E-mail',
        'phone' => 'Telefono',
        'street' => 'Via',
        'house_number' => 'Numero civico',
        'zip' => 'CAP',
        'city' => 'Città',
        'phone_hint' => 'Inserisci un numero di telefono valido, verrà verificato!',
        'phone_reachable' => 'Quando possiamo raggiungerti telefonicamente? (Solo per domande)',
        'reachable_allday' => 'tutto il giorno',
        'reachable_08_10' => '08:00 - 10:00',
        'reachable_10_12' => '10:00 - 12:00',
        'reachable_12_14' => '12:00 - 14:00',
        'reachable_14_18' => '14:00 - 18:00',
        'reachable_18_20' => '18:00 - 20:00',
        'confirm_request' => 'Conferma richiesta',
        'confirm_info' => 'Conferma la tua richiesta. Riceverai un SMS o un\'email con un codice di conferma.',
        'summary' => 'Riepilogo',
        'services' => 'Servizi',
        'contact' => 'Contatto',
        'address' => 'Indirizzo',
        'accept_terms' => 'Accetto i',
        'terms' => 'Termini',
        'and' => 'e',
        'privacy' => 'Privacy Policy',
        'back' => 'Indietro',
        'next' => 'Avanti',
        'submit' => 'Invia richiesta',
    ],
];

$lang = $sessionData['lang'] ?? 'de';
if (!isset($translations[$lang])) {
    $lang = 'de';
}
$t = $translations[$lang];

// Header-Konfiguration
$headerBgColor = $siteConfig->headerBackgroundColor ?? '#6c757d';
$logoUrl = $siteConfig->logoUrl;
$logoHeight = $siteConfig->logoHeightPixel ?? '60';

// Sprachen mit Flaggen-URLs
$languages = [
    'de' => ['name' => 'Deutsch', 'flag' => 'https://offertenschweiz.ch/wp-content/plugins/sitepress-multilingual-cms/res/flags/de.svg'],
    'en' => ['name' => 'English', 'flag' => 'https://offertenschweiz.ch/wp-content/plugins/sitepress-multilingual-cms/res/flags/en.svg'],
    'fr' => ['name' => 'Français', 'flag' => 'https://offertenschweiz.ch/wp-content/plugins/sitepress-multilingual-cms/res/flags/fr.svg'],
    'it' => ['name' => 'Italiano', 'flag' => 'https://offertenschweiz.ch/wp-content/plugins/sitepress-multilingual-cms/res/flags/it.svg'],
];
?>

<!-- Header mit Logo, Flaggen und Branchenfarbe -->
<header class="py-3 mb-4" style="background-color: <?= esc($headerBgColor) ?>;">
    <div class="container">
        <div class="d-flex align-items-center justify-content-center gap-4">
            <!-- Flaggen (Desktop) -->
            <div class="d-none d-md-flex gap-2">
                <?php foreach ($languages as $code => $langInfo): ?>
                <a href="#" title="<?= esc($langInfo['name']) ?>" class="<?= $code === $lang ? 'opacity-100' : 'opacity-75' ?>">
                    <img src="<?= esc($langInfo['flag']) ?>" alt="<?= esc($langInfo['name']) ?>" width="24" height="16" style="border-radius: 2px;">
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Logo -->
            <?php if ($logoUrl): ?>
            <a href="<?= esc($siteConfig->frontendUrl) ?>">
                <img src="<?= esc($logoUrl) ?>"
                     alt="<?= esc($siteConfig->name) ?>"
                     style="max-height: <?= esc($logoHeight) ?>px; max-width: 100%;">
            </a>
            <?php else: ?>
            <a href="<?= esc($siteConfig->frontendUrl) ?>" class="text-white text-decoration-none fs-4 fw-bold">
                <?= esc($siteConfig->name) ?>
            </a>
            <?php endif; ?>

            <!-- Flaggen (Desktop rechts) -->
            <div class="d-none d-md-flex gap-2">
                <?php foreach ($languages as $code => $langInfo): ?>
                <a href="#" title="<?= esc($langInfo['name']) ?>" class="<?= $code === $lang ? 'opacity-100' : 'opacity-75' ?>" style="visibility: hidden;">
                    <img src="<?= esc($langInfo['flag']) ?>" alt="<?= esc($langInfo['name']) ?>" width="24" height="16" style="border-radius: 2px;">
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</header>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Fortschrittsanzeige -->
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <?php
                    $steps = [
                        'termin' => $t['termin'],
                        'auftraggeber' => $t['auftraggeber'],
                        'kontakt' => $t['kontakt'],
                        'verify' => $t['verify']
                    ];
                    $stepKeys = array_keys($steps);
                    $currentStepIndex = array_search($step, $stepKeys);
                    ?>
                    <?php foreach ($steps as $key => $label): ?>
                        <?php
                        $stepIndex = array_search($key, $stepKeys);
                        $isActive = ($key === $step);
                        $isDone = $stepIndex < $currentStepIndex;
                        ?>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1
                                <?= $isActive ? 'bg-primary text-white' : ($isDone ? 'bg-success text-white' : 'bg-light text-muted') ?>"
                                 style="width: 32px; height: 32px;">
                                <?= $isDone ? '<i class="bi bi-check"></i>' : ($stepIndex + 1) ?>
                            </div>
                            <div class="small <?= $isActive ? 'fw-bold' : 'text-muted' ?>"><?= $label ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar" style="width: <?= (($currentStepIndex + 1) / count($steps)) * 100 ?>%"></div>
                </div>
            </div>

            <!-- Zusammenfassung der gewählten Dienstleistungen -->
            <div class="alert alert-light mb-4">
                <strong><?= $t['selected_services'] ?>:</strong>
                <?php foreach ($sessionData['form_links'] as $i => $link): ?>
                    <span class="badge bg-secondary me-1"><?= esc($link['name']) ?></span>
                <?php endforeach; ?>
            </div>

            <form method="post" action="<?= site_url('/request/save-finalize') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="session" value="<?= esc($sessionId) ?>">
                <input type="hidden" name="step" value="<?= esc($step) ?>">

                <?php if ($step === 'termin'): ?>
                    <!-- SCHRITT: Termin -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label for="datum" class="form-label fw-bold"><?= $t['when_start'] ?> <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="datum" name="datum" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                </div>
                                <div class="col-md-7 mb-3">
                                    <label class="form-label fw-bold"><?= $t['time_flexible'] ?> <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php
                                        $flexOptions = [
                                            'no' => $t['flex_no'],
                                            '1_2_days' => $t['flex_1_2_days'],
                                            '1_2_weeks' => $t['flex_1_2_weeks'],
                                            '1_month' => $t['flex_1_month'],
                                            'arrangement' => $t['flex_arrangement'],
                                        ];
                                        foreach ($flexOptions as $value => $label):
                                        ?>
                                            <div class="form-check form-check-inline p-0 m-0">
                                                <input type="radio" class="btn-check" name="zeit_flexibel" id="flex_<?= $value ?>" value="<?= $value ?>" autocomplete="off" required>
                                                <label class="btn btn-outline-dark" for="flex_<?= $value ?>"><?= $label ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($step === 'auftraggeber'): ?>
                    <!-- SCHRITT: Auftraggeber -->
                    <div class="card">
                        <div class="card-body">
                            <label class="form-label fw-bold"><?= $t['client_type'] ?> <span class="text-danger">*</span></label>
                            <div class="row g-2 mb-3">
                                <?php
                                $clientTypes = [
                                    'tenant' => $t['client_tenant'],
                                    'owner' => $t['client_owner'],
                                    'business' => $t['client_business'],
                                    'public' => $t['client_public'],
                                ];
                                foreach ($clientTypes as $value => $label):
                                ?>
                                    <div class="col-6 col-md-3">
                                        <input type="radio" class="btn-check" name="auftraggeber_typ" id="typ_<?= $value ?>" value="<?= $value ?>" autocomplete="off" required>
                                        <label class="btn btn-outline-dark w-100 py-3" for="typ_<?= $value ?>"><?= $label ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div id="firma_details" style="display: none;" class="mt-3">
                                <label for="firma" class="form-label"><?= $t['company_name'] ?></label>
                                <input type="text" class="form-control" id="firma" name="firma" placeholder="<?= $t['company_name'] ?>">
                            </div>
                        </div>
                    </div>

                    <script>
                    document.querySelectorAll('input[name="auftraggeber_typ"]').forEach(function(radio) {
                        radio.addEventListener('change', function() {
                            document.getElementById('firma_details').style.display =
                                (this.value === 'business' || this.value === 'public') ? 'block' : 'none';
                        });
                    });
                    </script>

                <?php elseif ($step === 'kontakt'): ?>
                    <!-- SCHRITT: Kontaktdaten -->
                    <div class="card">
                        <div class="card-body">
                            <!-- Name -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="vorname" class="form-label fw-bold"><?= $t['firstname'] ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="vorname" name="vorname" placeholder="<?= $t['firstname'] ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nachname" class="form-label fw-bold"><?= $t['lastname'] ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nachname" name="nachname" placeholder="<?= $t['lastname'] ?>" required>
                                </div>
                            </div>

                            <!-- E-Mail & Telefon -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-bold"><?= $t['email'] ?> <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="E-Mail-Adresse" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefon" class="form-label fw-bold">Telefonnummer <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">🇨🇭</span>
                                        <input type="tel" class="form-control" id="telefon" name="telefon" placeholder="Telefonnummer" required>
                                    </div>
                                    <small class="text-danger"><?= $t['phone_hint'] ?></small>
                                </div>
                            </div>

                            <!-- Strasse & Hausnummer -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="strasse" class="form-label fw-bold"><?= $t['street'] ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="strasse" name="strasse" placeholder="Musterstrasse" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="hausnummer" class="form-label fw-bold"><?= $t['house_number'] ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="hausnummer" name="hausnummer" placeholder="10" required>
                                </div>
                            </div>

                            <!-- PLZ & Ort -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="plz" class="form-label fw-bold"><?= $t['zip'] ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="plz" name="plz" placeholder="4000" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ort" class="form-label fw-bold"><?= $t['city'] ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ort" name="ort" placeholder="Basel" required>
                                </div>
                            </div>

                            <!-- Erreichbarkeit -->
                            <div class="mb-3">
                                <label class="form-label fw-bold"><?= $t['phone_reachable'] ?> <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <?php
                                    $reachableOptions = [
                                        'allday' => $t['reachable_allday'],
                                        '08_10' => $t['reachable_08_10'],
                                        '10_12' => $t['reachable_10_12'],
                                        '12_14' => $t['reachable_12_14'],
                                        '14_18' => $t['reachable_14_18'],
                                        '18_20' => $t['reachable_18_20'],
                                    ];
                                    foreach ($reachableOptions as $value => $label):
                                    ?>
                                        <div class="col-6 col-md-4">
                                            <input type="radio" class="btn-check" name="erreichbar" id="erreichbar_<?= $value ?>" value="<?= $value ?>" autocomplete="off" required>
                                            <label class="btn btn-outline-dark w-100" for="erreichbar_<?= $value ?>"><?= $label ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($step === 'verify'): ?>
                    <!-- SCHRITT: Verifikation -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><?= $t['confirm_request'] ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <?= $t['confirm_info'] ?>
                            </div>

                            <!-- Zusammenfassung -->
                            <h6><?= $t['summary'] ?>:</h6>
                            <ul class="list-unstyled">
                                <li><strong><?= $t['services'] ?>:</strong>
                                    <?php foreach ($sessionData['form_links'] as $link): ?>
                                        <?= esc($link['name']) ?><?= $link !== end($sessionData['form_links']) ? ', ' : '' ?>
                                    <?php endforeach; ?>
                                </li>
                                <?php if (!empty($sessionData['kontakt'])): ?>
                                <li><strong><?= $t['contact'] ?>:</strong> <?= esc($sessionData['kontakt']['vorname'] . ' ' . $sessionData['kontakt']['nachname']) ?></li>
                                <li><strong><?= $t['address'] ?>:</strong> <?= esc($sessionData['kontakt']['strasse'] . ', ' . $sessionData['kontakt']['plz'] . ' ' . $sessionData['kontakt']['ort']) ?></li>
                                <?php endif; ?>
                            </ul>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="agb" required>
                                <label class="form-check-label" for="agb">
                                    <?= $t['accept_terms'] ?> <a href="#" target="_blank"><?= $t['terms'] ?></a> <?= $t['and'] ?> <a href="#" target="_blank"><?= $t['privacy'] ?></a>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between mt-4">
                    <?php if ($step !== 'termin'): ?>
                        <a href="javascript:history.back()" class="btn text-white" style="background-color: <?= esc($headerBgColor) ?>;">
                            <?= $t['back'] ?>
                        </a>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-lg text-white" style="background-color: <?= esc($headerBgColor) ?>;">
                        <?= $step === 'verify' ? $t['submit'] : $t['next'] ?>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<style>
/* Ausgewählte Radio-Buttons bekommen Header-Farbe */
.btn-check:checked + .btn-outline-dark {
    background-color: <?= esc($headerBgColor) ?> !important;
    border-color: <?= esc($headerBgColor) ?> !important;
    color: white !important;
}
.btn-check:checked + .btn-outline-dark:hover {
    background-color: <?= esc($headerBgColor) ?> !important;
    border-color: <?= esc($headerBgColor) ?> !important;
}
</style>

<?= $this->endSection() ?>
