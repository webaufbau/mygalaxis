<h2><?= lang('Email.verifyPhoneTitle') ?></h2>

<p><?= sprintf(lang('Email.helloName'), esc($data['vorname'] ?? '')); ?></p>

<div class="highlight">
    <p><?= sprintf(lang('Email.phoneVerifyIntro'), esc($siteConfig->name)); ?></p>
    <p><?= lang('Email.phoneVerifyWarning'); ?></p>
</div>

<p><?= lang('Email.clickToVerify'); ?></p>

<p>
    <a href="<?= esc($verifyLink) ?>" class="button">
        <?= lang('Email.verifyNow'); ?>
    </a>
</p>

<p><?= lang('Email.thankYou'); ?></p>

<div class="footer">
    <?= sprintf(lang('Email.generatedAt'), date('d.m.Y H:i')); ?><br>
    <?= esc($siteConfig->name); ?>
</div>
