<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Verifizierung anfordern</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Verifizierung anfordern</h2>

    <?php if (session('error')): ?>
        <div class="alert alert-danger"><?= session('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('/verification/send') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="phone" class="form-label">Telefonnummer</label>
            <input type="text" name="phone" class="form-control" placeholder="+41..." required>
        </div>

        <div class="mb-3">
            <label class="form-label">Verifizierungsmethode</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="method" id="sms" value="sms" checked>
                <label class="form-check-label" for="sms">SMS</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="method" id="call" value="call">
                <label class="form-check-label" for="call">Anruf</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Verifizierung anfordern</button>
    </form>
</div>
</body>
</html>
