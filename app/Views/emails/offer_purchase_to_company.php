<h2><?= lang('Email.ThankYouTitle') ?></h2>

<p><?= lang('Email.SuccessMessage') ?></p>

<ul>
    <li><strong><?= lang('Email.Title') ?>:</strong> <?= esc($offer['title'] ?? '') ?></li>
    <li><strong><?= lang('Email.Type') ?>:</strong> <?= esc(lang('Offers.type.' . $offer['type']) ?? '') ?></li>
</ul>

<hr>

<h3><?= lang('Email.CustomerDataTitle') ?></h3>

<ul>
    <li><strong><?= lang('Email.Name') ?>:</strong> <?= esc($kunde['firstname'] ?? '') . ' ' . esc($kunde['lastname'] ?? '') ?></li>
    <li><strong><?= lang('Email.Email') ?>:</strong> <?= esc($kunde['email'] ?? '') ?></li>
    <li><strong><?= lang('Email.Phone') ?>:</strong> <?= esc($kunde['phone'] ?? '') ?></li>
</ul>

<p><?= lang('Email.ContactInstruction') ?></p>

<hr>

<h3><?= lang('Email.RequestSummaryTitle') ?></h3>

<?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => true]) ?>

<p style="margin-top: 30px;">
    <a href="<?= esc($company_backend_offer_link) ?>" style="background-color:#007BFF; color:#fff; padding:10px 15px; text-decoration:none; border-radius:5px;">
        <?= lang('Email.ViewOffer') ?>
    </a>
</p>
