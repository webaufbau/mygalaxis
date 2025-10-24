<p><?= lang('Email.greeting', [$firma->contact_person]) ?></p>

<p><?= lang('Email.newOfferIntro') ?></p>

<ul>
    <li>
        <strong><?= esc($offer['title'] ?? $offer['type']) ?></strong><br>
        <?= esc($offer['zip']) ?> <?= esc($offer['city'] ?? '') ?><br>
        Preis: <?= esc(number_format($offer['price'] ?? 0, 2)) ?> <?= esc($offer['currency'] ?? 'CHF') ?><br>
        <a href="<?= rtrim($siteConfig->backendUrl, '/') . '/offers/' . $offer['id'] ?>"><?= lang('Email.viewNow') ?></a>
    </li>
</ul>

<p><?= lang('Email.successWishes') ?></p>
