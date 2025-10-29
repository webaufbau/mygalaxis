<h2><?= lang('Email.ThankYouTitle') ?></h2>

<p><?= lang('Email.SuccessMessage') ?></p>

<ul>
    <li><strong><?= lang('Email.Title') ?>:</strong> <?= esc($offer['title'] ?? '') ?></li>
    <li><strong><?= lang('Email.Type') ?>:</strong> <?= esc(lang('Offers.type.' . $offer['type']) ?? '') ?></li>
</ul>

<hr>

<h3>Details zur gekauften Anfrage</h3>

<?php
// Extrahiere vollständige Kundendaten aus form_fields
$formFields = json_decode($offer['form_fields'] ?? '{}', true) ?? [];

// Name
$firstname = $formFields['vorname'] ?? $formFields['firstname'] ?? $kunde['firstname'] ?? '';
$lastname = $formFields['nachname'] ?? $formFields['lastname'] ?? $kunde['lastname'] ?? '';

// Kontakt
$email = $formFields['email'] ?? $formFields['e-mail'] ?? $formFields['e_mail'] ?? $kunde['email'] ?? '';
$phone = $formFields['telefon'] ?? $formFields['phone'] ?? $formFields['tel'] ?? $kunde['phone'] ?? '';
$mobile = $formFields['mobile'] ?? $formFields['handy'] ?? '';

// Adresse - prüfe auch verschachtelte Struktur
$street = '';
$houseNumber = '';

// Direkte Felder
$street = $formFields['strasse'] ?? $formFields['street'] ?? $formFields['adresse'] ?? $formFields['address_line_1'] ?? '';
$houseNumber = $formFields['hausnummer'] ?? $formFields['house_number'] ?? $formFields['address_line_2'] ?? '';

// Verschachtelte Adresse (z.B. $formFields['address']['address_line_1'])
if (empty($street) && isset($formFields['address']) && is_array($formFields['address'])) {
    $street = $formFields['address']['address_line_1'] ?? $formFields['address']['strasse'] ?? $formFields['address']['street'] ?? '';
    $houseNumber = $formFields['address']['address_line_2'] ?? $formFields['address']['hausnummer'] ?? '';
}

// Wenn Adresse selbst ein Array ist
if (is_array($street)) {
    $street = $street['strasse'] ?? $street['street'] ?? $street['address_line_1'] ?? '';
}
if (is_array($houseNumber)) {
    $houseNumber = $houseNumber['hausnummer'] ?? $houseNumber['house_number'] ?? $houseNumber['address_line_2'] ?? '';
}

$zip = $formFields['plz'] ?? $formFields['zip'] ?? $offer['zip'] ?? '';
$city = $formFields['ort'] ?? $formFields['city'] ?? $offer['city'] ?? '';

// Typ
$type = lang('Offers.type.' . $offer['type']);
if (str_starts_with($type, 'Offers.')) {
    $type = ucfirst(strtolower(str_replace(['_', '-'], ' ', $offer['type'])));
}
?>

<ul>
    <li><strong>Art der Anfrage:</strong> <?= esc($type) ?></li>

    <?php if (!empty($firstname) || !empty($lastname)): ?>
        <li><strong>Name des Kunden:</strong> <?= esc($firstname) ?> <?= esc($lastname) ?></li>
    <?php endif; ?>

    <?php if (!empty($email)): ?>
        <li><strong>E-Mail des Kunden:</strong> <a href="mailto:<?= esc($email) ?>"><?= esc($email) ?></a></li>
    <?php endif; ?>

    <?php if (!empty($phone)): ?>
        <li><strong>Telefon des Kunden:</strong> <a href="tel:<?= esc($phone) ?>"><?= esc($phone) ?></a></li>
    <?php endif; ?>

    <?php if (!empty($mobile)): ?>
        <li><strong>Mobil:</strong> <a href="tel:<?= esc($mobile) ?>"><?= esc($mobile) ?></a></li>
    <?php endif; ?>

    <?php if (!empty($street)): ?>
        <li><strong>Adresse des Kunden:</strong>
            <?= esc($street) ?>
            <?php if (!empty($houseNumber)): ?> <?= esc($houseNumber) ?><?php endif; ?>
        </li>
    <?php endif; ?>

    <?php if (!empty($zip) || !empty($city)): ?>
        <li><strong>Ort:</strong>
            <?php if (!empty($zip)): ?><?= esc($zip) ?> <?php endif; ?>
            <?= esc($city) ?>
        </li>
    <?php endif; ?>
</ul>

<p><?= lang('Email.ContactInstruction') ?></p>

<hr>

<h3><?= lang('Email.RequestSummaryTitle') ?></h3>

<?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => true, 'wrapInCard' => false]) ?>

<p style="margin-top: 30px;">
    <a href="<?= esc($company_backend_offer_link) ?>" style="background-color:#007BFF; color:#fff; padding:10px 15px; text-decoration:none; border-radius:5px;">
        <?= lang('Email.ViewOffer') ?>
    </a>
</p>
