<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc($title ?? 'Angebote') ?></h2>

<form method="get" class="row mb-4 g-3 align-items-center">
    <div class="col-auto">
        <input
                type="search"
                name="search"
                value="<?= esc($search) ?>"
                class="form-control"
                placeholder="Suchen..."
                aria-label="Suche"
        >
    </div>
    <div class="col-auto">
        <select name="filter" class="form-select">
            <option value="">Alle Status</option>
            <option value="available" <?= ($filter === 'available') ? 'selected' : '' ?>>Zum Kauf</option>
            <option value="sold" <?= ($filter === 'sold') ? 'selected' : '' ?>>Erledigt</option>
            <option value="out_of_stock" <?= ($filter === 'out_of_stock') ? 'selected' : '' ?>>Ausverkauft</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filtern</button>
    </div>
</form>

<?php if (empty($offers)): ?>
    <div class="alert alert-info">
        Keine Angebote gefunden. Bitte <a href="/filter" class="alert-link">erweitern Sie die Filter</a>, um mehr Angebote zu sehen.
    </div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($offers as $offer): ?>

                <?php
                $isPurchased = in_array($offer['id'], $purchasedOfferIds ?? []);
                $status = $offer['status'] ?? 'available';  // Status kann unabhängig bleiben

                $btnClass = 'btn-primary';
                $btnText = 'Zum Kauf';

                if ($isPurchased) {
                    $btnClass = 'btn-success';
                    $btnText = 'Details';
                } elseif ($status === 'out_of_stock') {
                    $btnClass = 'btn-danger disabled';
                    $btnText = 'Ausverkauft';
                }
                ?>


                <div class="list-group-item p-3 mb-3 border rounded bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1 me-3">
                            <span class="title fw-bold d-block"><?= esc($offer['title']) ?></span>
                            <small class="text-muted"><?= date('d.m.Y', strtotime($offer['created_at'])) ?></small>
                            <br>

                            <?php if (!$isPurchased && $status == 'available'): ?>
                                <a class="" data-bs-toggle="collapse" href="#details-<?= $offer['id'] ?>" role="button" aria-expanded="false" aria-controls="details-<?= $offer['id'] ?>" data-toggle-icon="#toggleIcon-<?= $offer['id'] ?>">
                                    <i class="bi bi-chevron-right" id="toggleIcon-<?= $offer['id'] ?>"></i> Anfragedetails anzeigen
                                </a>
                            <?php else: ?>
                                <p></p>
                            <?php endif; ?>
                        </div>

                        <div class="text-end" style="min-width: 150px;">
                            <?php
                            $createdDate = new DateTime($offer['created_at']);
                            $now = new DateTime();
                            $diffDays = $now->diff($createdDate)->days;

                            $displayPrice = $offer['price'];
                            $priceWasDiscounted = false;
                            if ($diffDays > 3) {
                                $displayPrice = $offer['price'] / 2;
                                $priceWasDiscounted = true;
                            }
                            ?>
                            <div class="small">
                                <?php if ($priceWasDiscounted): ?>
                                    <span class="text-decoration-line-through text-muted me-2"><?= number_format($offer['price'], 2) ?> CHF</span>
                                    <span><?= number_format($displayPrice, 2) ?> CHF</span>
                                <?php else: ?>
                                    <?= number_format($displayPrice, 2) ?> CHF
                                <?php endif; ?>
                            </div>

                            <?php if (!$isPurchased && $status === 'available'): ?>
                                <a href="<?= site_url('offers/buy/' . $offer['id']) ?>" class="btn btn-primary btn-sm mt-2">Kaufen</a>
                            <?php elseif ($isPurchased): ?>
                                <a href="<?= site_url('offers/mine#detailsview-' . $offer['id']) ?>" class="btn btn-primary btn-sm mt-2"><?=$btnText;?></a>
                            <?php else: ?>
                                <button type="button" class="btn <?= $btnClass ?> btn-sm mt-2" disabled><?= $btnText ?></button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="collapse mt-3" id="details-<?= $offer['id'] ?>">
                        <div class="card card-body bg-light">
                            <?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => false]) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(link => {
        const targetSelector = link.getAttribute('href');
        const iconSelector = link.getAttribute('data-toggle-icon');
        const icon = document.querySelector(iconSelector);
        const target = document.querySelector(targetSelector);

        if (!target || !icon) return;

        target.addEventListener('show.bs.collapse', () => {
            icon.classList.remove('bi-chevron-right');
            icon.classList.add('bi-chevron-down');
        });

        target.addEventListener('hide.bs.collapse', () => {
            icon.classList.remove('bi-chevron-down');
            icon.classList.add('bi-chevron-right');
        });
    });

</script>


<?= $this->endSection() ?>
