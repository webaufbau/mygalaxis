<h2><?= lang('Email.ThankYouTitle') ?></h2>

<p><?= lang('Email.SuccessMessage') ?></p>

<ul>
    <li><strong><?= lang('Email.Title') ?>:</strong> <?= esc($offer['title'] ?? '') ?></li>
    <li><strong><?= lang('Email.Type') ?>:</strong> <?= esc(lang('Offers.type.' . $offer['type']) ?? '') ?></li>
</ul>

<hr>

<h3><?= lang('Email.CustomerDataTitle') ?></h3>

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

// Adresse
$street = $formFields['strasse'] ?? $formFields['street'] ?? $formFields['adresse'] ?? '';
$houseNumber = $formFields['hausnummer'] ?? $formFields['house_number'] ?? '';
$zip = $formFields['plz'] ?? $formFields['zip'] ?? $offer['zip'] ?? '';
$city = $formFields['ort'] ?? $formFields['city'] ?? $offer['city'] ?? '';

// Wenn Adresse ein Array ist (verschachtelt), versuche es zu extrahieren
if (is_array($street)) {
    $street = $street['strasse'] ?? $street['street'] ?? '';
}
if (is_array($houseNumber)) {
    $houseNumber = $houseNumber['hausnummer'] ?? $houseNumber['house_number'] ?? '';
}
?>

<ul>
    <?php if (!empty($firstname) || !empty($lastname)): ?>
        <li><strong><?= lang('Email.Name') ?>:</strong> <?= esc($firstname) ?> <?= esc($lastname) ?></li>
    <?php endif; ?>

    <?php if (!empty($email)): ?>
        <li><strong><?= lang('Email.Email') ?>:</strong> <a href="mailto:<?= esc($email) ?>"><?= esc($email) ?></a></li>
    <?php endif; ?>

    <?php if (!empty($phone)): ?>
        <li><strong><?= lang('Email.Phone') ?>:</strong> <a href="tel:<?= esc($phone) ?>"><?= esc($phone) ?></a></li>
    <?php endif; ?>

    <?php if (!empty($mobile)): ?>
        <li><strong>Mobil:</strong> <a href="tel:<?= esc($mobile) ?>"><?= esc($mobile) ?></a></li>
    <?php endif; ?>

    <?php if (!empty($street)): ?>
        <li><strong>Strasse:</strong>
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
