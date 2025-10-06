<p><?= lang('Email.greeting', [$firma->contact_person]) ?></p>

<p><?= lang('Email.priceUpdateIntro') ?></p>

<ul>
    <li>
        <strong><?= esc($offer['title']) ?></strong><br>
        <?= esc($offer['zip']) ?> <?= esc($offer['city']) ?><br>
        <?= lang('Email.oldPrice') ?>: <?= esc($oldPrice) ?> CHF<br>
        <?= lang('Email.newPrice') ?>: <?= esc($newPrice) ?> CHF<br>
        (<?= $discount ?>% <?= lang('Email.discountApplied') ?>)<br>
        <a href="<?= site_url('/offers/' . $offer['id']) ?>">
            <?= lang('Email.viewNow') ?>
        </a>
    </li>
</ul>

<p><?= lang('Email.successWishes') ?></p>

<p><?= lang('Email.greetings', [siteconfig()->name]) ?></p>
