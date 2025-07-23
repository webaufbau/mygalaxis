<h2>Eine Firma interessiert sich für Ihre Anfrage</h2>

<ul>
    <li><strong>Firma:</strong> <?= esc($firma->company_name ?? $firma->firstname . ' ' . $firma->lastname) ?></li>
    <li><strong>Ansprechperson:</strong> <?= esc($firma->contact_person ?? '') ?></li>
    <li><strong>E-Mail:</strong> <?= esc($firma->company_email ?? $firma->email ?? '') ?></li>
    <li><strong>Telefon:</strong> <?= esc($firma->company_phone ?? $firma->phone ?? '') ?></li>
    <li><strong>Adresse:</strong> <?= esc(implode(', ', array_filter([$firma->company_street ?? '', $firma->company_zip ?? '', $firma->company_city ?? '']))) ?></li>
    <?php if (!empty($firma->company_website)): ?>
        <li><strong>Webseite:</strong> <a href="<?= esc($firma->company_website) ?>" target="_blank"><?= esc($firma->company_website) ?></a></li>
    <?php endif; ?>
</ul>

<hr>

<h3>Details zur Ihrer Anfrage</h3>

<ul>
    <li><strong>Art der Anfrage:</strong> <?= esc(lang('Offers.type.' . $offer['type'] ?? 'Unbekannt')) ?></li>
    <li><strong>Ihr Name:</strong> <?= esc(($offer['firstname'] ?? '') . ' ' . ($offer['lastname'] ?? '')) ?></li>
    <li><strong>Ihre E-Mail:</strong> <?= esc($offer['email'] ?? '') ?></li>
    <li><strong>Ihre Telefonnummer:</strong> <?= esc($offer['phone'] ?? '') ?></li>
</ul>

<hr>

<p>Sie können hier die Firmen sehen, die Ihre Anfrage gekauft haben, und diese nach Abschluss der Arbeiten bewerten:</p>

<p>
    <a href="<?= esc($interessentenLink) ?>" style="background-color:#007BFF; color:#fff; padding:10px 15px; text-decoration:none; border-radius:5px;">
        Firmen ansehen
    </a>
</p>

<p>Vielen Dank für Ihr Vertrauen!</p>
