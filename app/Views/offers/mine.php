<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Offers.my_offers_title')) ?></h2>

<?php if (empty($offers)): ?>
    <div class="alert alert-info"><?= lang('Offers.none_found') ?></div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($offers as $offer): ?>
            <div class="list-group-item p-3 mb-3 border rounded bg-white">

                <?php
                $status = $offer['status'] ?? 'available';
                $btnClass = 'btn-primary';
                $btnText = lang('Offers.toBuy');

                if ($status === 'sold') {
                    $btnClass = 'btn-success disabled';
                    $btnText = lang('Offers.done');
                } elseif ($status === 'out_of_stock') {
                    $btnClass = 'btn-danger disabled';
                    $btnText = lang('Offers.sold_out');
                }
                ?>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1 me-3">
                        <span class="title fw-bold d-block"><?= esc($offer['title']) ?></span>
                        <small class="text-muted">
                            <?= lang('Offers.purchased_on') ?> <?= \CodeIgniter\I18n\Time::parse($offer['purchased_at'])->setTimezone(app_timezone())->format('d.m.Y') ?>
                        </small>
                        <br>

                        <a id="detailsview-<?= $offer['id'] ?>"
                           data-bs-toggle="collapse"
                           href="#details-<?= $offer['id'] ?>"
                           role="button"
                           aria-expanded="false"
                           aria-controls="details-<?= $offer['id'] ?>"
                           data-toggle-icon="#toggleIcon-<?= $offer['id'] ?>">
                            <i class="bi bi-chevron-right" id="toggleIcon-<?= $offer['id'] ?>"></i>
                            <?= lang('Offers.show_request_details') ?>
                        </a>
                    </div>

                    <div class="text-end" style="min-width: 150px;">
                        <?php
                        if (isset($offer['purchased_price'])) {
                            $originalPrice = $offer['price'];
                            $discountedPrice = $offer['discounted_price'];

                            if ($offer['purchased_price'] == $originalPrice) {
                                echo '<div class="small">' . lang('Offers.price_normal') . '</div>';
                            } elseif ($offer['purchased_price'] == $discountedPrice) {
                                echo '<div class="small">' . lang('Offers.price_discounted') . '</div>';
                            } else {
                                echo '<div class="small">' . lang('Offers.price_purchased') . '</div>';
                            }
                        } else {
                            $createdDate = new DateTime($offer['created_at']);
                            $now = new DateTime();
                            $diffDays = $now->diff($createdDate)->days;

                            $displayPrice = $offer['price'];
                            $priceWasDiscounted = false;
                            if ($offer['discounted_price'] > 0) {
                                $displayPrice = $offer['discounted_price'];
                                $priceWasDiscounted = true;
                            }
                            ?>
                            <div class="small">
                                <?php if ($priceWasDiscounted): ?>
                                    <span class="text-decoration-line-through text-muted me-2">
                                        <?= number_format($offer['price'], 2) ?> CHF
                                    </span>
                                    <span><?= number_format($displayPrice, 2) ?> CHF</span>
                                <?php else: ?>
                                    <?= number_format($displayPrice, 2) ?> CHF
                                <?php endif; ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>

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


    $(document).ready(function () {
        // Prüfen ob Hash in URL vorhanden
        const hash = window.location.hash;

        if (hash && hash.startsWith('#detailsview-')) {
            // ID des Toggles aus dem Hash
            const toggleLink = $(hash);
            if (toggleLink.length) {
                // Ziel-Collapse ermitteln (href-Attribut des Links)
                const targetSelector = toggleLink.attr('href');
                const targetCollapse = $(targetSelector);

                // Collapse mit Bootstrap öffnen
                if (targetCollapse.length) {
                    // Bootstrap Collapse über JS öffnen (wenn Bootstrap 5)
                    const collapseInstance = bootstrap.Collapse.getOrCreateInstance(targetCollapse[0]);
                    collapseInstance.show();

                    // Scrollen zum Toggle-Link (optional mit Offset wegen fixiertem Header)
                    const offset = 70; // anpassen falls nötig
                    const pos = toggleLink.offset().top - offset;

                    $('html, body').animate({ scrollTop: pos }, 500);
                }
            }
        }
    });


</script>


<?= $this->endSection() ?>
