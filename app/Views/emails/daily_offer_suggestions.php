<p>Guten Tag <?= esc($firma->company_name) ?> <?= esc($firma->contact_person) ?>,</p>

<p>Hier sind neue Offerten, die kürzlich bei uns eingegangen sind und zu Ihren Filterkriterien passen:</p>

<ul>
    <?php foreach ($offers as $offer): ?>
        <li>
            <strong><?= esc($offer['title']) ?></strong><br>
            <?= esc($offer['zip']) ?> <?= esc($offer['city']) ?><br>
            <a href="<?= site_url('/offers#details-' . $offer['id']) ?>">Jetzt ansehen</a>
        </li>
    <?php endforeach; ?>
</ul>

<p>Wir wünschen viel Erfolg bei der Auswahl passender Aufträge!</p>

<p>Freundliche Grüsse<br>Ihr <?=$siteConfig->name;?>-Team</p>
