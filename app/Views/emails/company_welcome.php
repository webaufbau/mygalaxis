
    <h1>Willkommen bei <?=$website_name;?>, <?= esc($contact_person) ?>!</h1>
    <p>Vielen Dank für Ihre Anmeldung als Firma. Ihr Konto ist nun aktiv und Sie können direkt loslegen.</p>

    <h2>Ihr Firmenbereich im Überblick:</h2>

    <h3>1️⃣ Übersicht</h3>
    <p>Hier sehen Sie alle Aktivitäten Ihres Kontos, gekaufte Angebote, Kaufdatum und Beträge.</p>

    <h3>2️⃣ Filter</h3>
    <p>Legen Sie fest, welche Angebote für Sie interessant sind:</p>
    <ul>
        <li>Branchen: z.B. Umzug, Reinigung, Maler, Gartenpflege, Sanitär usw.</li>
        <li><?php
            $regionLabel = match($country ?? 'ch') {
                'de' => 'Bundesländer',
                'at' => 'Bundesländer',
                default => 'Kantone'
            };
            echo $regionLabel;
        ?> & Regionen: z.B. <?php
            $examples = match($country ?? 'ch') {
                'de' => 'Bayern, Baden-Württemberg, Nordrhein-Westfalen usw.',
                'at' => 'Wien, Niederösterreich, Oberösterreich usw.',
                default => 'Aargau, Basel, Zürich usw.'
            };
            echo $examples;
        ?></li>
    </ul>

    <h3>3️⃣ Offene Anfragen</h3>
    <p>Alle aktuellen Angebote, die noch verfügbar sind. Sehen Sie sich Details, Ort, Datum und Preis an und kaufen Sie passende Anfragen.</p>

    <h3>4️⃣ Finanzen</h3>
    <p>Verwalten Sie Ihr Guthaben, sehen Sie Ihre Transaktionen und behalten Sie Ausgaben und Einnahmen im Blick.</p>

    <h3>5️⃣ Agenda</h3>
    <p>Planen Sie Ihre Aktivitäten und blockieren Sie E-Mails an bestimmten Tagen, z.B. Wochenenden oder Urlaub.</p>

    <h3>6️⃣ Mein Konto</h3>
    <p>Verwalten Sie Ihre Firmeninformationen, Spracheinstellungen und aktivieren oder deaktivieren Sie den automatischen Kauf passender Angebote.</p>

    <h3>7️⃣ Bewertungen</h3>
    <p>Erhalten Sie Feedback von Kunden, sehen Sie Ihre durchschnittliche Bewertung und die Anzahl der gekauften Anfragen.</p>

    <h3>8️⃣ Abmelden</h3>
    <p>Loggen Sie sich sicher aus Ihrem Konto aus.</p>

    <p>Wir wünschen Ihnen viel Erfolg mit <?=$website_name;?>! 💼</p>

    <a href="<?= esc($backend_url) ?>" class="button">Zu Ihrem Dashboard</a>

    <p style="font-size:12px;color:#888888;">
        <?=$website_name;?> – Ihr Portal für passende Firmenanfragen.
        Bei Fragen wenden Sie sich an <a href="mailto:<?=esc($website_email);?>"><?=esc($website_email);?></a>.
    </p>

<style>

    h1, h2, h3 {
        color: #0056b3;
    }
    h1 {
        font-size: 24px;
    }
    h2 {
        font-size: 20px;
        margin-top: 25px;
    }
    h3 {
        font-size: 16px;
        margin-top: 15px;
    }
    p {
        line-height: 1.6;
    }
    ul {
        padding-left: 20px;
    }
    .button {
        display: inline-block;
        padding: 10px 20px;
        margin: 20px 0;
        background-color: #0056b3;
        color: #ffffff;
        text-decoration: none;
        border-radius: 5px;
    }
    .highlight {
        color: #e63946;
        font-weight: bold;
    }
</style>
