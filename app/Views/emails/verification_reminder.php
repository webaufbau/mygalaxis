
    <h2>👋 Bitte bestätige deine Telefonnummer</h2>

    <p>Hallo <?= esc($data['vorname'] ?? ''); ?>,</p>

    <div class="highlight">
        <p>Du hast kürzlich eine Anfrage über <?=$siteConfig->name;?> gestellt, aber die Verifizierung deiner Telefonnummer wurde noch nicht abgeschlossen.</p>
        <p>Ohne diese Bestätigung kann deine Anfrage nicht weiterverarbeitet werden.</p>
    </div>

    <p>Klicke bitte auf den folgenden Button, um zur Verifizierungsseite zu gelangen:</p>

    <p><a href="<?= esc($verifyLink) ?>" class="button">Jetzt bestätigen</a></p>

    <p>Vielen Dank für deine Mithilfe!</p>

    <div class="footer">
        Diese Nachricht wurde automatisch generiert am <?= date('d.m.Y H:i') ?>.<br>
        <?=$siteConfig->name;?>
    </div>
