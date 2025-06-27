<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Code eingeben</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Code eingeben</h2>

    <?php if (session('error')): ?>
        <div class="alert alert-danger"><?= session('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('/verification/verify') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="code" class="form-label">Bestätigungscode</label>
            <input type="text" name="code" class="form-control" placeholder="123456" required>
        </div>

        <button type="submit" class="btn btn-success">Verifizieren</button>

        <?php /*
        <p>[Zu Demozwecken Code ist: <?=$verification_code;?>] (wird in der Testphase nicht veröffentlicht)</p>
 */ ?>

    </form>
</div>
</body>
</html>
