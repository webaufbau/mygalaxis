<h2><?= lang('Email.verifyPhoneTitle') ?></h2>

<p><?= lang('Email.helloName', [esc($data['vorname'] ?? $data['names'] ?? 'Kunde')]) ?></p>

<div class="highlight">
    <p><?= lang('Email.phoneVerifyIntro', [esc($siteConfig->name)]) ?></p>
    <p><?= lang('Email.phoneVerifyWarning') ?></p>
</div>

<?php if (isset($isMultiple) && $isMultiple): ?>
    <!-- Mehrere Offerten -->
    <p>Sie haben <?= count($offers) ?> Anfragen gestellt. Bitte bestätigen Sie Ihre Telefonnummer für folgende Anfragen:</p>

    <div style="margin: 20px 0;">
        <?php foreach ($offers as $index => $offer): ?>
            <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; background-color: #f9f9f9;">
                <h3 style="margin-top: 0;">Anfrage <?= $index + 1 ?>: <?= esc(ucfirst($offer['type'])) ?></h3>
                <p>
                    <a href="<?= esc($offer['verifyLink']) ?>" class="button" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                        Jetzt bestätigen
                    </a>
                </p>
            </div>
        <?php endforeach; ?>
    </div>

<?php else: ?>
    <!-- Einzelne Offerte -->
    <p><?= lang('Email.clickToVerify') ?></p>

    <p>
        <a href="<?= esc($offers[0]['verifyLink']) ?>" class="button">
            <?= lang('Email.verifyNow') ?>
        </a>
    </p>
<?php endif; ?>

<p><?= lang('Email.thankYou') ?></p>

<div class="footer">
    <?= lang('Email.generatedAt', [date('d.m.Y H:i')]) ?><br>
    <?= esc($siteConfig->name) ?>
</div>
