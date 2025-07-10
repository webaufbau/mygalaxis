<?php
// Defensiv vorbereiten, falls Daten fehlen
$displayName = $buyer['company_name'] ?? ($buyer['username'] ?? 'Unbekannt');
$mitgliedSeit = isset($buyer['created_at']) ? date('d.m.Y', strtotime($buyer['created_at'])) : 'Unbekannt';
?>

<p>Guten Tag</p>

<p>Ihre Anfrage <strong><?= esc($offer['form_name']) ?></strong> wurde soeben von folgender Firma gekauft:</p>

<ul>
    <li><strong><?= esc($displayName) ?></strong></li>
    <li>Mitglied seit: <?= esc($mitgliedSeit) ?></li>
</ul>

<p>Sie können nun entscheiden, ob Sie den Auftrag an diese Firma vergeben möchten:</p>

<p><a href="<?= $giveJobLink ?>">➤ Ja, Auftrag erteilen</a></p>

<p>Nach Abschluss können Sie auch eine Bewertung abgeben:</p>

<p><a href="<?= $reviewLink ?>">➤ Jetzt bewerten</a></p>

<p>Freundliche Grüsse<br>
    Ihr <?= esc($_ENV['app.name'] ?? 'Team') ?></p>
