<h2><?= lang('Email.title_firm_interest') ?></h2>

<ul>
    <li><strong><?= lang('Email.label_company') ?>:</strong> <?= esc($firma->company_name ?? $firma->firstname . ' ' . $firma->lastname) ?></li>
    <li><strong><?= lang('Email.label_contact') ?>:</strong> <?= esc($firma->contact_person ?? '') ?></li>
    <li><strong><?= lang('Email.label_email') ?>:</strong> <?= esc($firma->company_email ?? $firma->email ?? '') ?></li>
    <li><strong><?= lang('Email.label_phone') ?>:</strong> <?= esc($firma->company_phone ?? $firma->phone ?? '') ?></li>
    <li><strong><?= lang('Email.label_address') ?>:</strong> <?= esc(implode(', ', array_filter([$firma->company_street ?? '', $firma->company_zip ?? '', $firma->company_city ?? '']))) ?></li>
    <?php if (!empty($firma->company_website)): ?>
        <li><strong><?= lang('Email.label_website') ?>:</strong> <a href="<?= esc($firma->company_website) ?>" target="_blank"><?= esc($firma->company_website) ?></a></li>
    <?php endif; ?>
</ul>

<hr>

<h3><?= lang('Email.section_request') ?></h3>

<ul>
    <li><strong><?= lang('Email.label_request_type') ?>:</strong> <?= esc(lang('Offers.type.' . ($offer['type'] ?? 'unknown'))) ?></li>
    <li><strong><?= lang('Email.label_your_name') ?>:</strong> <?= esc(($offer['firstname'] ?? '') . ' ' . ($offer['lastname'] ?? '')) ?></li>
    <li><strong><?= lang('Email.label_your_email') ?>:</strong> <?= esc($offer['email'] ?? '') ?></li>
    <li><strong><?= lang('Email.label_your_phone') ?>:</strong> <?= esc($offer['phone'] ?? '') ?></li>
    <?php
    // Extrahiere Adresse aus form_fields
    $formFields = json_decode($offer['form_fields'] ?? '{}', true);
    $addressParts = [];
    $addressKeys = ['strasse', 'street', 'address_line_1'];
    $houseKeys = ['hausnummer', 'house_number', 'nummer'];

    $street = '';
    $house = '';

    foreach ($formFields as $key => $value) {
        if (is_array($value) && (strpos(strtolower($key), 'adresse') !== false || strpos(strtolower($key), 'address') !== false)) {
            foreach ($value as $subKey => $subValue) {
                $normalizedSubKey = str_replace([' ', '-'], '_', strtolower($subKey));
                if (in_array($normalizedSubKey, $addressKeys) && !empty($subValue)) {
                    $street = $subValue;
                }
                if (in_array($normalizedSubKey, $houseKeys) && !empty($subValue)) {
                    $house = $subValue;
                }
            }
        }

        $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));
        if (in_array($normalizedKey, $addressKeys) && !empty($value) && !is_array($value)) {
            $street = $value;
        }
        if (in_array($normalizedKey, $houseKeys) && !empty($value) && !is_array($value)) {
            $house = $value;
        }
    }

    if (!empty($street)):
        $address = $street;
        if (!empty($house)) {
            $address .= ' ' . $house;
        }
    ?>
    <li><strong>Adresse:</strong> <?= esc($address) ?></li>
    <?php endif; ?>
    <?php if (!empty($offer['zip']) || !empty($offer['city'])): ?>
    <li><strong>Ort:</strong>
        <?php if (!empty($offer['zip'])): ?><?= esc($offer['zip']) ?> <?php endif; ?>
        <?= esc($offer['city'] ?? '') ?>
    </li>
    <?php endif; ?>
</ul>

<h4>Anfragedetails:</h4>
<?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => true, 'wrapInCard' => false]) ?>

<hr>

<p><?= lang('Email.see_companies_text') ?></p>

<p>
    <a href="<?= esc($interessentenLink) ?>" style="background-color:#007BFF; color:#fff; padding:10px 15px; text-decoration:none; border-radius:5px;">
        <?= lang('Email.see_companies') ?>
    </a>
</p>

<p><?= lang('Email.thanks') ?></p>
