<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { display: inline-block; padding: 12px 24px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h2><?= esc(lang('Profile.emailChangeConfirmHeading')) ?></h2>

        <p><?= sprintf(esc(lang('Profile.emailChangeConfirmGreeting')), esc($user->company_name)) ?></p>

        <p><?= esc(lang('Profile.emailChangeConfirmText')) ?></p>

        <p>
            <a href="<?= esc($confirmUrl) ?>" class="button">
                <?= esc(lang('Profile.emailChangeConfirmButton')) ?>
            </a>
        </p>

        <p><?= esc(lang('Profile.emailChangeConfirmManualLink')) ?><br>
            <a href="<?= esc($confirmUrl) ?>"><?= esc($confirmUrl) ?></a>
        </p>

        <p><strong><?= sprintf(esc(lang('Profile.emailChangeConfirmExpiry')), date('d.m.Y H:i', strtotime($expiresAt))) ?></strong></p>

        <p><?= esc(lang('Profile.emailChangeConfirmIgnore')) ?></p>

        <div class="footer">
            <p><?= lang('Profile.emailFooter') ?></p>
        </div>
    </div>
</body>
</html>
