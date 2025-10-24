<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Offers.title')) ?></h2>

<form method="get" class="row mb-4 g-3 align-items-center">
    <div class="col-auto">
        <input
                type="search"
                name="search"
                value="<?= esc($search) ?>"
                class="form-control"
                placeholder="<?= lang('Offers.searchPlaceholder') ?>"
                aria-label="<?= lang('Offers.searchAriaLabel') ?>"
        >
    </div>
    <div class="col-auto">
        <select name="filter" class="form-select" onchange="this.form.submit()">
            <option value=""><?= lang('Offers.allStatuses') ?></option>
            <option value="available" <?= ($filter === 'available') ? 'selected' : '' ?>><?= lang('Offers.filterAvailable') ?></option>
            <option value="purchased" <?= ($filter === 'purchased') ? 'selected' : '' ?>><?= lang('Offers.filterPurchased') ?></option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary"><?= lang('Offers.filterButton') ?></button>
    </div>
</form>

<?php if (empty($offers)): ?>
    <div class="alert alert-info">
        <?= lang('Offers.noOffersFound') ?> <a href="/filter" class="alert-link"><?= lang('Offers.expandFilters') ?></a>.
    </div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($offers as $offer): ?>

            <?php
            $isPurchased = in_array($offer['id'], $purchasedOfferIds ?? []);
            $status = $offer['status'] ?? 'available';

            $btnClass = 'btn-primary';
            $btnText = lang('Offers.statusAvailable');

            if ($isPurchased) {
                $btnClass = 'btn-success';
                $btnText = lang('Offers.detailsButton');
            } elseif ($status === 'out_of_stock') {
                $btnClass = 'btn-danger disabled';
                $btnText = lang('Offers.statusOutOfStock');
            }
            ?>

            <div class="list-group-item p-3 mb-3 border rounded <?= $isPurchased ? 'bg-success bg-opacity-10 border-success' : 'bg-white' ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1 me-3">
                        <span class="title fw-bold d-block">
                            <?= $isPurchased ? '<i class="bi bi-check-circle-fill text-success me-1"></i>' : '' ?>
                            <?= esc($offer['title']) ?>
                        </span>
                        <small class="text-muted"><?= date('d.m.Y - H:i', strtotime($offer['created_at'])) ?><?= !empty(lang('Offers.time_suffix')) ? ' ' . lang('Offers.time_suffix') : '' ?></small>
                        <br>

                        <?php if ($status == 'available' || $isPurchased): ?>
                            <a data-bs-toggle="collapse" href="#details-<?= $offer['id'] ?>" role="button" aria-expanded="false" aria-controls="details-<?= $offer['id'] ?>" data-toggle-icon="#toggleIcon-<?= $offer['id'] ?>">
                                <i class="bi bi-chevron-right" id="toggleIcon-<?= $offer['id'] ?>"></i> <?= lang('Offers.showDetails') ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="text-end" style="min-width: 150px;">
                        <?php
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
                                <span class="text-decoration-line-through text-muted me-2"><?= number_format($offer['price'], 2) ?> <?= currency() ?></span>
                                <span><?= number_format($displayPrice, 2) ?> <?= currency() ?></span>
                            <?php else: ?>
                                <?= number_format($displayPrice, 2) ?> <?= currency() ?>
                            <?php endif; ?>
                        </div>

                        <?php if (!$isPurchased && $status === 'available' && $displayPrice > 0): ?>
                            <a href="<?= site_url('offers/buy/' . $offer['id']) ?>" class="btn btn-primary btn-sm mt-2"><?= lang('Offers.buyButton') ?></a>
                        <?php elseif ($isPurchased): ?>
                            <a href="<?= site_url('offers/' . $offer['id']) ?>" class="btn btn-primary btn-sm mt-2"><?= $btnText; ?></a>
                        <?php elseif ($displayPrice <= 0): ?>
                            <button type="button" class="btn btn-secondary btn-sm mt-2" disabled><?= lang('Offers.priceNotAvailable') ?></button>
                        <?php else: ?>
                            <button type="button" class="btn <?= $btnClass ?> btn-sm mt-2" disabled><?= $btnText ?></button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="collapse mt-3" id="details-<?= $offer['id'] ?>">
                    <div class="card card-body bg-light">
                        <?php if ($isPurchased): ?>
                            <?php
                            // Kundeninfos extrahieren (wie in show.php)
                            $customerInfo = [];
                            $formFields = json_decode($offer['form_fields'] ?? '', true) ?? [];
                            $contactKeys = [
                                'vorname' => 'Vorname',
                                'firstname' => 'Vorname',
                                'first_name' => 'Vorname',
                                'nachname' => 'Nachname',
                                'lastname' => 'Nachname',
                                'last_name' => 'Nachname',
                                'surname' => 'Nachname',
                                'email' => 'E-Mail',
                                'e_mail' => 'E-Mail',
                                'email_address' => 'E-Mail',
                                'mail' => 'E-Mail',
                                'e_mail_adresse' => 'E-Mail',
                                'telefon' => 'Telefon',
                                'telefonnummer' => 'Telefon',
                                'phone' => 'Telefon',
                                'telephone' => 'Telefon',
                                'phone_number' => 'Telefon',
                                'tel' => 'Telefon'
                            ];

                            foreach ($formFields as $key => $value) {
                                $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));
                                if (isset($contactKeys[$normalizedKey]) && !empty($value)) {
                                    $label = $contactKeys[$normalizedKey];
                                    if (!isset($customerInfo[$label])) {
                                        $customerInfo[$label] = $value;
                                    }
                                }
                            }
                            ?>

                            <?php if (!empty($customerInfo)): ?>
                                <!-- Kundeninformationen prominent anzeigen -->
                                <div class="mb-3 pb-3 border-bottom">
                                    <h5 class="mb-2"><i class="bi bi-person-circle text-success"></i> Kundeninformationen</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php foreach ($customerInfo as $label => $value): ?>
                                                <p class="mb-1">
                                                    <strong><?= esc($label) ?>:</strong>
                                                    <?php if ($label === 'E-Mail'): ?>
                                                        <a href="mailto:<?= esc($value) ?>"><?= esc($value) ?></a>
                                                    <?php elseif ($label === 'Telefon'): ?>
                                                        <a href="tel:<?= esc($value) ?>"><?= esc($value) ?></a>
                                                    <?php else: ?>
                                                        <?= esc($value) ?>
                                                    <?php endif; ?>
                                                </p>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <strong><?= lang('Offers.labels.zip') ?>:</strong> <?= esc($offer['zip']) ?>
                                            </p>
                                            <p class="mb-1">
                                                <strong><?= lang('Offers.labels.city') ?>:</strong> <?= esc($offer['city']) ?>
                                            </p>
                                            <p class="mb-1">
                                                <strong><?= lang('Offers.labels.type') ?>:</strong> <?= lang('Offers.type.' . $offer['type']) ?>
                                            </p>
                                            <?php if (!empty($offer['purchased_at'])): ?>
                                                <p class="text-muted mb-0">
                                                    <small><?= lang('Offers.purchased_on') ?>: <?= date('d.m.Y', strtotime($offer['purchased_at'])) ?></small>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => $isPurchased]) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if (isset($pager)): ?>
        <div class="mt-4">
            <?= $pager->links('default', 'bootstrap5') ?>
        </div>
    <?php endif; ?>
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
