<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc($title ?? 'Angebote') ?></h2>

<?php if (empty($offers)): ?>
    <div class="alert alert-info">Keine Angebote gefunden.</div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($offers as $offer): ?>
            <div class="list-group-item p-3 mb-3 border rounded bg-white">

                <?php
                $status = $offer['status'] ?? 'available';
                $btnClass = 'btn-primary';
                $btnText = 'Zum Kauf';

                if ($status === 'sold') {
                    $btnClass = 'btn-success disabled';
                    $btnText = 'Erledigt';
                } elseif ($status === 'out_of_stock') {
                    $btnClass = 'btn-danger disabled';
                    $btnText = 'Ausverkauft';
                }


                ?>


                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1 me-3">
                        <span class="title fw-bold d-block">

                                <?= esc($offer['title']) ?>

                        </span>
                        <small class="text-muted">Gekauft am <?= date('d.m.Y', strtotime($offer['purchased_at'])) ?></small>
                        <br>

                        <?php if($status == 'available') { ?>
                        <!-- Toggle-Link für Details -->
                            <a class=" " data-bs-toggle="collapse" href="#details-<?= $offer['id'] ?>" role="button" aria-expanded="false" aria-controls="details-<?= $offer['id'] ?>" data-toggle-icon="#toggleIcon-<?= $offer['id'] ?>">
                                <i class="bi bi-chevron-right" id="toggleIcon-<?= $offer['id'] ?>"></i> Anfragedetails anzeigen
                            </a>

                        <?php } else { echo "<p></p>"; } ?>


                    </div>

                    <div class="text-end" style="min-width: 150px;">
                        <?php
                        // Prüfen, ob gekauft (purchased_price vorhanden)
                        if (isset($offer['purchased_price'])) {
                            // Ermittlung, ob Original- oder reduzierter Preis gekauft wurde
                            $originalPrice = $offer['price'];
                            $discountedPrice = $originalPrice / 2;

                            if ($offer['purchased_price'] == $originalPrice) {
                                echo '<div class="small  ">Normal</div>';
                            } elseif ($offer['purchased_price'] == $discountedPrice) {
                                echo '<div class="small  ">Reduziert</div>';
                            } else {
                                echo '<div class="small  ">Gekauft</div>';
                            }

                        } else {
                            // Noch nicht gekauft — zeige regulären Preis mit ggf. Rabatt
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
                                    <span class="text-decoration-line-through text-muted me-2">
                    <?= number_format($offer['price'], 2) ?> CHF
                </span>
                                    <span class="text-">
                    <?= number_format($displayPrice, 2) ?> CHF
                </span>
                                <?php else: ?>
                                    <?= number_format($displayPrice, 2) ?> CHF
                                <?php endif; ?>
                            </div>
                        <?php } ?>
                    </div>

                </div>

                <!-- Collapsible Details -->
                <div class="collapse mt-3" id="details-<?= $offer['id'] ?>">
                    <div class="card card-body bg-light">
                        <?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => $isOwnView ?? false]) ?>
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
