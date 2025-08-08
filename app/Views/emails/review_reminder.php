<h2><?= lang('Reviews.reminderTitle') ?></h2>

<p><?= sprintf(lang('Reviews.helloName'), esc($creatorFirstname ?? '')) ?></p>

<div class="highlight">
    <p><?= sprintf(lang('Reviews.reminderIntro'), esc($offerTitle ?? ''), esc($siteConfig->name)) ?></p>
    <p><?= lang('Reviews.reminderWarning') ?></p>
</div>

<p><?= lang('Reviews.clickToReview') ?></p>

<p>
    <a href="<?= esc($reviewLink) ?>" class="button">
        <?= lang('Reviews.reviewNow') ?>
    </a>
</p>

<p><?= lang('Reviews.thankYou') ?></p>

<div class="footer">
    <?= sprintf(lang('Reviews.generatedAt'), date('d.m.Y H:i')) ?><br>
    <?= esc($siteConfig->name ?? '') ?>
</div>
