<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4">Bewertungen</h2>

<!-- Zusammenfassung -->
<div class="mb-4">
    <div class="row">
        <div class="col-md-4">
            <h4>⭑ Durchschnitt:</h4>
            <div class="d-flex align-items-center">
                <?php
                $rounded = round($avgRating, 1);
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
            <h4>Anfragen gekauft:</h4>
            <p><?= $totalPurchased+30 ?></p>
        </div>
        <div class="col-md-4">
            <h4>Erhaltene Bewertungen:</h4>
            <p><?= $totalReviews ?></p>
        </div>
    </div>
</div>

<!-- Bewertungen -->
<?php if (empty($reviews)): ?>
    <div class="alert alert-info">Noch keine Bewertungen erhalten.</div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($reviews as $review): ?>
            <div class="list-group-item bg-white border rounded mb-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong><?= date('d.m.Y', strtotime($review['created_at'])) ?></strong><br>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi <?= $i <= $review['rating'] ? 'bi-star-fill text-warning' : 'bi-star text-secondary' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <a class="btn btn-sm " data-bs-toggle="collapse" href="#comment-<?= $review['id'] ?>" aria-expanded="false" aria-controls="comment-<?= $review['id'] ?>">
                        Kommentar anzeigen
                    </a>
                </div>
                <div class="collapse mt-3" id="comment-<?= $review['id'] ?>">
                    <div class="card card-body bg-light">
                        <?= esc($review['comment'] ?: 'Kein Kommentar.') ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        <?= $pager->links('default', 'bootstrap') ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
