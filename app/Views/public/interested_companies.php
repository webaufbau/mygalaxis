<?= $this->extend('layout/public') ?>
<?= $this->section('content') ?>

<?php if ($alreadyRated): ?>
    <h1 class="mb-4"><?= lang('InterestedCompanies.requestCompleted') ?> <?= esc($offer['title']) ?></h1>

    <p class="text-success mt-2"><em><?= lang('InterestedCompanies.thanksForRating') ?></em></p>
<?php else: ?>

    <h1 class="mb-4"><?= lang('InterestedCompanies.interestedCompaniesFor') ?> <?= esc($offer['title']) ?></h1>

    <div class="card mb-4">
        <div class="card-body">
            <?php if (!empty($offer['description'])): ?>
                <p class="card-text"><?= esc($offer['description']) ?></p>
            <?php endif; ?>
            <?php if (!empty($offer['work_start_date'])): ?>
                <p class="text-muted mb-0">
                    <?= lang('InterestedCompanies.plannedWorkStart') ?>:
                    <strong><?= date('d.m.Y', strtotime($offer['work_start_date'])) ?></strong>
                </p>
            <?php endif; ?>

            <a class="" data-bs-toggle="collapse" href="#details-<?= $offer['id'] ?>" role="button" aria-expanded="false" aria-controls="details-<?= $offer['id'] ?>" data-toggle-icon="#toggleIcon-<?= $offer['id'] ?>">
                <i class="bi bi-chevron-right" id="toggleIcon-<?= $offer['id'] ?>"></i> <?= lang('InterestedCompanies.showRequestDetails') ?>
            </a>

            <div class="collapse mt-3" id="details-<?= $offer['id'] ?>">
                <div class="card card-body bg-light">
                    <?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => true]) ?>
                </div>
            </div>
        </div>
    </div>

    <p class="mb-4 text-muted">
        <?= lang('InterestedCompanies.infoText') ?>
    </p>

    <?php if (empty($companies)): ?>
        <div class="alert alert-info">
            <?= lang('InterestedCompanies.noCompanies') ?>
        </div>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($companies as $company): ?>
                <li class="list-group-item">
                    <h5 class="mb-1"><?= esc($company->company_name) ?></h5>

                    <?php if (!empty($company->contact_person)): ?>
                        <p class="mb-0"><strong><?= lang('InterestedCompanies.contact') ?>:</strong> <?= esc($company->contact_person) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($company->company_email)): ?>
                        <p class="mb-0"><strong><?= lang('InterestedCompanies.email') ?>:</strong>
                            <a href="mailto:<?= esc($company->company_email) ?>"><?= esc($company->company_email) ?></a>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($company->company_phone)): ?>
                        <p class="mb-0"><strong><?= lang('InterestedCompanies.phone') ?>:</strong> <?= esc($company->company_phone) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($company->company_street) || !empty($company->company_zip) || !empty($company->company_city)): ?>
                        <p class="mb-0">
                            <strong><?= lang('InterestedCompanies.address') ?>:</strong>
                            <?= esc($company->company_street) ?>,
                            <?= esc($company->company_zip) ?> <?= esc($company->company_city) ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($company->average_rating)): ?>
                        <p class="mt-2 mb-0">
                            <strong><?= lang('InterestedCompanies.ratings') ?>:</strong>
                            <?php
                            $stars = floor($company->average_rating);
                            for ($i = 0; $i < 5; $i++):
                                if ($i < $stars):
                                    echo '<i class="bi bi-star-fill text-warning"></i>';
                                else:
                                    echo '<i class="bi bi-star text-muted"></i>';
                                endif;
                            endfor;
                            ?>
                            <small class="text-muted">(<?= number_format($company->average_rating, 1) ?>/5)</small>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($company->reviews)): ?>
                        <div class="mt-2">
                            <strong><?= lang('InterestedCompanies.lastReviews') ?>:</strong>
                            <ul class="list-unstyled">
                                <?php foreach (array_slice($company->reviews, 0, 3) as $review): ?>
                                    <li class="mb-2">
                                        <small class="text-muted"><?= date('d.m.Y', strtotime($review->created_at)) ?></small><br>
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <?php if ($i < $review->rating): ?>
                                                <i class="bi bi-star-fill text-warning"></i>
                                            <?php else: ?>
                                                <i class="bi bi-star text-muted"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <?php if (!empty($review->comment)): ?>
                                            <div><?= esc($review->comment) ?></div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!$alreadyRated): ?>
                        <hr>
                        <form method="post" action="<?= site_url('/rating/submit') ?>" class="mt-3">
                            <?= csrf_field() ?>

                            <input type="hidden" name="offer_token" value="<?= esc($offer['access_hash']) ?>">
                            <input type="hidden" name="recipient_id" value="<?= esc($company->id) ?>">

                            <div class="mb-2">
                                <label class="form-label"><?= lang('InterestedCompanies.yourRating') ?>:</label><br>
                                <div class="rating-stars" data-company="<?= $company->id ?>">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" name="rating" id="rating-<?= $company->id ?>-<?= $i ?>" value="<?= $i ?>" class="d-none">
                                        <label for="rating-<?= $company->id ?>-<?= $i ?>" class="star" data-value="<?= $i ?>">
                                            <i class="bi bi-star-fill text-muted"></i>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label for="comment-<?= $company->id ?>" class="form-label"><?= lang('InterestedCompanies.comment') ?>:</label>
                                <textarea class="form-control" name="comment" id="comment-<?= $company->id ?>" rows="2" required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm"><?= lang('InterestedCompanies.submitRating') ?></button>
                        </form>
                    <?php endif; ?>

                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

<?php endif; ?>

<div class="mt-4">
    <a href="<?= esc($siteConfig->frontendUrl) ?>" class="btn btn-secondary"><?= lang('InterestedCompanies.backToHomepage') ?></a>
</div>

<style>
    .rating-stars .star {
        cursor: pointer;
        font-size: 1.5rem;
        margin-right: 2px;
    }
</style>

<script>
    document.querySelectorAll('.rating-stars').forEach(ratingContainer => {
        const stars = ratingContainer.querySelectorAll('.star');
        const radios = ratingContainer.querySelectorAll('input[type=radio]');

        // Standardmässig 5 Sterne aktivieren
        const defaultRating = 5;
        radios[defaultRating - 1].checked = true;
        stars.forEach((s, index) => {
            const icon = s.querySelector('i');
            if (index < defaultRating) {
                icon.classList.remove('text-muted');
                icon.classList.add('text-warning');
            } else {
                icon.classList.add('text-muted');
                icon.classList.remove('text-warning');
            }
        });

        // Klick-Verhalten
        stars.forEach(star => {
            star.addEventListener('click', () => {
                const selectedValue = parseInt(star.dataset.value);
                stars.forEach((s, index) => {
                    const icon = s.querySelector('i');
                    if (index < selectedValue) {
                        icon.classList.remove('text-muted');
                        icon.classList.add('text-warning');
                    } else {
                        icon.classList.add('text-muted');
                        icon.classList.remove('text-warning');
                    }
                });
                radios[selectedValue - 1].checked = true;
            });
        });
    });
</script>

<?= $this->endSection() ?>
