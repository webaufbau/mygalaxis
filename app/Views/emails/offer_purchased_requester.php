<p>Guten Tag</p>

<p>Ihre Anfrage <strong><?= esc($offer['form_name']) ?></strong> wurde soeben von folgender Firma gekauft:</p>

<ul>
    <li><strong><?= esc($buyer['company_name'] ?? $buyer['username']) ?></strong></li>
    <li>Mitglied seit: <?= date('d.m.Y', strtotime($buyer['created_at'])) ?></li>
</ul>

<p>Sie können nun entscheiden, ob Sie den Auftrag an diese Firma vergeben möchten:</p>

<p><a href="<?= $giveJobLink ?>">➤ Ja, Auftrag erteilen</a></p>

<p>Nach Abschluss können Sie auch eine Bewertung abgeben:</p>

<p><a href="<?= $reviewLink ?>">➤ Jetzt bewerten</a></p>

<p>Freundliche Grüsse<br>
    Ihr <?= $_ENV['app.name'] ?? 'Team' ?></p>
