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
</ul>

<hr>

<p><?= lang('Email.see_companies_text') ?></p>

<p>
    <a href="<?= esc($interessentenLink) ?>" style="background-color:#007BFF; color:#fff; padding:10px 15px; text-decoration:none; border-radius:5px;">
        <?= lang('Email.see_companies') ?>
    </a>
</p>

<p><?= lang('Email.thanks') ?></p>
