<?php
/** @var array $data */
/** @var string $verifyLink */
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Bitte bestätige deine Telefonnummer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6f6f6;
            padding: 20px;
            color: #333;
        }

        .container {
            background: #ffffff;
            max-width: 700px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #0054a6;
        }

        .button {
            display: inline-block;
            padding: 12px 20px;
            background-color: #0054a6;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .footer {
            margin-top: 40px;
            font-size: 0.9em;
            color: #777;
            text-align: center;
        }

        .highlight {
            background: #e8f4ff;
            padding: 15px;
            border-left: 4px solid #0054a6;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>👋 Bitte bestätige deine Telefonnummer</h2>

    <p>Hallo <?= esc($data['vorname'] ?? ''); ?>,</p>

    <div class="highlight">
        <p>Du hast kürzlich eine Anfrage über Offertenschweiz gestellt, aber die Verifizierung deiner Telefonnummer wurde noch nicht abgeschlossen.</p>
        <p>Ohne diese Bestätigung kann deine Anfrage nicht weiterverarbeitet werden.</p>
    </div>

    <p>Klicke bitte auf den folgenden Button, um zur Verifizierungsseite zu gelangen:</p>

    <p><a href="<?= esc($verifyLink) ?>" class="button">Jetzt bestätigen</a></p>

    <p>Vielen Dank für deine Mithilfe!</p>

    <div class="footer">
        Diese Nachricht wurde automatisch generiert am <?= date('d.m.Y H:i') ?>.<br>
        Offerten Schweiz
    </div>
</div>
</body>
</html>
