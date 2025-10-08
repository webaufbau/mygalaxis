<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title><?=$title ?? '';?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background: #f6f6f6;
            padding: 20px;
            line-height: 1.5;
        }

        .container {
            background: #ffffff;
            padding: 30px;
            max-width: 700px;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        h1, h2, h3 {
            color: #0054a6;
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        li {
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #eee;
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
            padding: 10px;
            border-left: 4px solid #0054a6;
            margin-bottom: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="container">

    <?= $content ?? '' ?>

    <div class="footer">
        <?php
        // Verwende übergebene $siteConfig oder Fallback zu siteconfig()
        $config = $siteConfig ?? siteconfig();
        $signature = $config->emailSignature ?? '';
        if (!empty($signature)):
        ?>
            <p><?= nl2br(esc($signature)) ?></p>
        <?php else: ?>
            <p><?= lang('Email.greetings', [$config->name]) ?></p>
        <?php endif; ?>
        <p style="font-size: 0.85em; color: #999; margin-top: 20px;">
            <?= lang('Email.automaticGenerated') ?> <?= date('d.m.Y H:i') ?>.
        </p>
    </div>

</div>
</body>
</html>
