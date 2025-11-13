<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Reviews.title')) ?></h2>

<!-- Zusammenfassung -->
<div class="mb-4">
    <div class="row">
        <div class="col-md-4">
            <h4>⭑ <?= esc(lang('Reviews.average')) ?>:</h4>
            <div class="d-flex align-items-center">
                <?php
                $rounded = round($avgReview, 1);
                $fullStars = floor($rounded);
                $halfStar = $rounded - $fullStars >= 0.5;
                ?>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi <?= $i <= $fullStars ? 'bi-star-fill text-warning' : ($halfStar && $i == $fullStars + 1 ? 'bi-star-half text-warning' : 'bi-star text-secondary') ?>"></i>
                <?php endfor; ?>
                <span class="ms-2 fw-bold"><?= number_format($rounded, 1) ?>/5</span>
            </div>
        </div>
        <div class="col-md-4">
            <h4><?= esc(lang('Reviews.purchasedRequests')) ?>:</h4>
            <p><?= $totalPurchased ?></p>
        </div>
        <div class="col-md-4">
            <h4><?= esc(lang('Reviews.receivedReviews')) ?>:</h4>
            <p><?= $totalReviews ?></p>
        </div>
    </div>
</div>

<!-- Bewertungen -->
<?php if (empty($reviews)): ?>
    <div class="alert alert-info"><?= esc(lang('Reviews.noReviewsYet')) ?></div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($reviews as $review): ?>
            <div class="list-group-item bg-white border rounded mb-3">
                <!-- Anfrage-Info -->
                <?php if (isset($review->offer) && $review->offer): ?>
                    <div class="mb-2">
                        <strong class="text-primary">
                            <i class="bi bi-file-text me-1"></i>
                            <?= esc($review->offer['title'] ?? 'Anfrage') ?>
                        </strong>
                        <span class="text-muted ms-2">
                            <i class="bi bi-calendar me-1"></i>
                            <?= date('d.m.Y', strtotime($review->offer['created_at'] ?? $review->created_at)) ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">
                            <i class="bi bi-chat-left-text me-1"></i>Bewertet am: <?= date('d.m.Y', strtotime($review->created_at)) ?>
                        </small>
                        <br>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi <?= $i <= $review->rating ? 'bi-star-fill text-warning' : 'bi-star text-secondary' ?>"></i>
                        <?php endfor; ?>
                        <span class="ms-2 fw-bold"><?= $review->rating ?>/5</span>
                    </div>
                    <?php if (!empty($review->comment)): ?>
                        <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" href="#comment-<?= $review->id ?>" aria-expanded="false" aria-controls="comment-<?= $review->id ?>">
                            <i class="bi bi-chat-square-text me-1"></i><?= esc(lang('Reviews.showComment')) ?>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($review->comment)): ?>
                    <div class="collapse mt-3" id="comment-<?= $review->id ?>">
                        <div class="card card-body bg-light">
                            <i class="bi bi-quote text-muted"></i>
                            <?= esc($review->comment) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        <?= $pager->links('default', 'bootstrap5') ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
