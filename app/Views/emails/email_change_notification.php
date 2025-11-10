<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .alert { padding: 15px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; margin: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h2><?= esc(lang('Profile.emailChangeNotificationHeading')) ?></h2>

        <p><?= sprintf(esc(lang('Profile.emailChangeNotificationGreeting')), esc($user->company_name)) ?></p>

        <div class="alert">
            <p><strong><?= esc(lang('Profile.emailChangeNotificationAlert')) ?></strong></p>
            <p><?= sprintf(esc(lang('Profile.emailChangeNotificationNewEmail')), esc($newEmail)) ?></p>
        </div>

        <p><?= esc(lang('Profile.emailChangeNotificationConfirmText')) ?></p>

        <p><?= esc(lang('Profile.emailChangeNotificationNotYou')) ?></p>

        <div class="footer">
            <p><?= lang('Profile.emailFooter') ?></p>
        </div>
    </div>
</body>
</html>
