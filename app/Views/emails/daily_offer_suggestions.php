<p><?= lang('Email.greeting', [$firma->contact_person]) ?></p>

<p><?= lang('Email.intro') ?></p>

<ul>
    <?php foreach ($offers as $offer): ?>
        <li>
            <strong><?= esc($offer['title']) ?></strong><br>
            <?= esc($offer['zip']) ?> <?= esc($offer['city']) ?><br>
            <a href="<?= site_url('/offers#details-' . $offer['id']) ?>"><?= lang('Email.viewNow') ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<p><?= lang('Email.successWishes') ?></p>

<p><?= lang('Email.greetings') ?></p>
