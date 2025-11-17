<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Offers.title')) ?></h2>

<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong><?= lang('General.note') ?? 'Note' ?>:</strong> <?= lang('Offers.info_text') ?>
</div>

<form method="get" class="mb-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-funnel me-2"></i><strong><?= lang('Offers.filter_title') ?></strong>
        </div>
        <div class="card-body">
            <!-- Branchen-Filter (Mehrfachauswahl als Buttons) -->
            <div class="mb-3">
                <label class="form-label fw-bold"><?= lang('Offers.filter_categories_label') ?></label>
                <?php if (empty($categoryTypes)): ?>
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= lang('Offers.filter_no_categories') ?>
                        <a href="<?= site_url('filter') ?>" class="alert-link"><?= lang('Offers.filter_no_categories_link') ?></a>,
                        <?= lang('Offers.filter_no_categories_suffix') ?>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($categoryTypes as $typeKey => $typeName): ?>
                            <?php $isSelected = in_array($typeKey, $selectedTypes); ?>
                            <div>
                                <input
                                    type="checkbox"
                                    class="btn-check"
                                    name="types[]"
                                    value="<?= esc($typeKey) ?>"
                                    id="type_<?= esc($typeKey) ?>"
                                    autocomplete="off"
                                    <?= $isSelected ? 'checked' : '' ?>
                                >
                                <label class="btn btn-outline-primary" for="type_<?= esc($typeKey) ?>">
                                    <i class="bi bi-tag me-1"></i><?= esc($typeName) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i><?= lang('Offers.filter_categories_info') ?> <a href="<?= site_url('filter') ?>"><?= lang('Offers.filter_no_categories_link') ?></a> <?= lang('Offers.filter_categories_info_suffix') ?>
                    </small>
                <?php endif; ?>
            </div>

            <!-- Status-Filter -->
            <div class="mb-3">
                <label class="form-label fw-bold"><?= lang('Offers.filter_status_label') ?></label>
                <select name="filter" class="form-select">
                    <option value=""><?= lang('Offers.allStatuses') ?></option>
                    <option value="available" <?= ($filter === 'available') ? 'selected' : '' ?>><?= lang('Offers.filterAvailable') ?></option>
                    <option value="purchased" <?= ($filter === 'purchased') ? 'selected' : '' ?>><?= lang('Offers.filterPurchased') ?></option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i><?= lang('Offers.filterButton') ?>
                </button>
                <a href="<?= site_url('offers') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i><?= lang('Offers.filter_reset') ?>
                </a>
            </div>
        </div>
    </div>
</form>

<!-- Statistiken -->
<div class="card mb-4">
    <div class="card-header bg-info text-white" data-bs-toggle="collapse" data-bs-target="#statsCollapse" style="cursor: pointer;">
        <i class="bi bi-bar-chart me-2"></i><strong><?= lang('Offers.stats.title') ?></strong>
        <i class="bi bi-chevron-down float-end" style="transition: transform 0.3s ease;"></i>
    </div>
    <div id="statsCollapse" class="collapse <?= (auth()->user()->stats_always_open || ($fromYear && $toYear)) ? 'show' : '' ?>">
        <div class="card-body">
        <?php if (!empty($statsError)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= esc($statsError) ?>
            </div>
        <?php endif; ?>

        <!-- Zeitraum-Filter -->
        <form method="get" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-bold"><?= lang('Offers.stats.from_month') ?></label>
                    <select name="from_month" class="form-select">
                        <option value="">-</option>
                        <?php
                        $months = lang('General.months');
                        for ($m = 1; $m <= 12; $m++):
                        ?>
                            <option value="<?= $m ?>" <?= ($fromMonth == $m) ? 'selected' : '' ?>>
                                <?= $months[$m] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold"><?= lang('Offers.stats.year') ?> *</label>
                    <select name="from_year" class="form-select" required>
                        <option value="">-</option>
                        <?php
                        $currentYear = (int)date('Y');
                        $startYear = max(2025, $currentYear - 5); // Start bei 2025 (Plattform-Start)
                        $endYear = $currentYear + 2; // 2 Jahre in die Zukunft
                        for ($y = $endYear; $y >= $startYear; $y--):
                        ?>
                            <option value="<?= $y ?>" <?= ($fromYear == $y) ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold"><?= lang('Offers.stats.to_month') ?></label>
                    <select name="to_month" class="form-select">
                        <option value="">-</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($toMonth == $m) ? 'selected' : '' ?>>
                                <?= $months[$m] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold"><?= lang('Offers.stats.year') ?> *</label>
                    <select name="to_year" class="form-select" required>
                        <option value="">-</option>
                        <?php
                        for ($y = $endYear; $y >= $startYear; $y--):
                        ?>
                            <option value="<?= $y ?>" <?= ($toYear == $y) ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-1"></i><?= lang('Offers.stats.filter_button') ?>
                    </button>
                    <a href="<?= site_url('offers') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i><?= lang('Offers.stats.reset_button') ?>
                    </a>
                </div>
            </div>
        </form>

        <hr>

        <!-- Gefilterte Statistiken (nur wenn Zeitraum gesetzt) -->
        <?php if ($fromYear && $toYear): ?>
        <div class="alert alert-light border mb-3">
            <h6 class="mb-3">
                <i class="bi bi-calendar-range me-2"></i><?= lang('Offers.stats.period') ?>:
                <?php if ($fromMonth): ?>
                    <?= $months[$fromMonth] ?>
                <?php endif; ?>
                <?= $fromYear ?>
                -
                <?php if ($toMonth): ?>
                    <?= $months[$toMonth] ?>
                <?php endif; ?>
                <?= $toYear ?>
            </h6>
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="card bg-success bg-opacity-10 border-success h-100">
                        <div class="card-body">
                            <h6 class="card-title"><?= lang('Offers.stats.purchased') ?></h6>
                            <p class="mb-1"><strong><?= number_format($filteredStats['purchased']['count'], 0, ',', "'") ?> <?= lang('Offers.stats.pieces') ?></strong></p>
                            <p class="mb-1"><?= lang('Offers.stats.total') ?>: <?= currency() ?> <?= number_format($filteredStats['purchased']['total'], 2, '.', "'") ?></p>
                            <p class="mb-0 text-muted"><?= lang('Offers.stats.average') ?> <?= currency() ?> <?= number_format($filteredStats['purchased']['avg'], 2, '.', "'") ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning bg-opacity-10 border-warning h-100">
                        <div class="card-body">
                            <h6 class="card-title"><?= lang('Offers.stats.not_purchased') ?></h6>
                            <p class="mb-1"><strong><?= number_format($filteredStats['not_purchased']['count'], 0, ',', "'") ?> <?= lang('Offers.stats.pieces') ?></strong></p>
                            <p class="mb-1"><?= lang('Offers.stats.total') ?>: <?= currency() ?> <?= number_format($filteredStats['not_purchased']['total'], 2, '.', "'") ?></p>
                            <p class="mb-0 text-muted"><?= lang('Offers.stats.average') ?> <?= currency() ?> <?= number_format($filteredStats['not_purchased']['avg'], 2, '.', "'") ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-primary bg-opacity-10 border-primary h-100">
                        <div class="card-body">
                            <h6 class="card-title"><?= lang('Offers.stats.total_order_value') ?></h6>
                            <p class="mb-1"><strong><?= currency() ?> <?= number_format($filteredStats['purchased']['total'] + $filteredStats['not_purchased']['total'], 2, '.', "'") ?></strong></p>
                            <p class="mb-0 text-muted"><?= lang('Offers.stats.in_period') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fix-Statistiken (seit Registrierung) -->
        <h6 class="mb-3">
            <i class="bi bi-infinity me-2"></i><?= lang('Offers.stats.total_since_registration') ?>
        </h6>
        <div class="row text-center">
            <div class="col-md-6">
                <div class="card bg-success bg-opacity-10 border-success h-100">
                    <div class="card-body">
                        <h6 class="card-title"><?= lang('Offers.stats.total_purchased') ?></h6>
                        <p class="mb-1"><strong><?= number_format($totalStats['purchased']['count'], 0, ',', "'") ?> <?= lang('Offers.stats.pieces') ?></strong></p>
                        <p class="mb-1"><?= lang('Offers.stats.total') ?>: <?= currency() ?> <?= number_format($totalStats['purchased']['total'], 2, '.', "'") ?></p>
                        <p class="mb-0 text-muted"><?= lang('Offers.stats.average') ?> <?= currency() ?> <?= number_format($totalStats['purchased']['avg'], 2, '.', "'") ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-warning bg-opacity-10 border-warning h-100">
                    <div class="card-body">
                        <h6 class="card-title"><?= lang('Offers.stats.total_not_purchased') ?></h6>
                        <p class="mb-1"><strong><?= number_format($totalStats['not_purchased']['count'], 0, ',', "'") ?> <?= lang('Offers.stats.pieces') ?></strong></p>
                        <p class="mb-1"><?= lang('Offers.stats.total') ?>: <?= currency() ?> <?= number_format($totalStats['not_purchased']['total'], 2, '.', "'") ?></p>
                        <p class="mb-0 text-muted"><?= lang('Offers.stats.average') ?> <?= currency() ?> <?= number_format($totalStats['not_purchased']['avg'], 2, '.', "'") ?></p>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

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
                            <?= esc($offer['dynamic_title'] ?? $offer['title']) ?>
                        </span>
                        <small class="text-muted">
                            <?= date('d.m.Y - H:i', strtotime($offer['created_at'])) ?><?= !empty(lang('Offers.time_suffix')) ? ' ' . lang('Offers.time_suffix') : '' ?> ·
                            <span class="badge bg-secondary"><?= lang('Offers.order_number') ?> #<?= $offer['id'] ?></span>
                        </small>
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
                            $addressInfo = [];
                            $formFields = json_decode($offer['form_fields'] ?? '', true) ?? [];
                            $contactKeys = [
                                'vorname' => lang('General.firstName'),
                                'firstname' => lang('General.firstName'),
                                'first_name' => lang('General.firstName'),
                                'nachname' => lang('General.lastName'),
                                'lastname' => lang('General.lastName'),
                                'last_name' => lang('General.lastName'),
                                'surname' => lang('General.lastName'),
                                'email' => lang('General.email'),
                                'e_mail' => lang('General.email'),
                                'email_address' => lang('General.email'),
                                'mail' => lang('General.email'),
                                'e_mail_adresse' => lang('General.email'),
                                'telefon' => lang('General.phone'),
                                'telefonnummer' => lang('General.phone'),
                                'phone' => lang('General.phone'),
                                'telephone' => lang('General.phone'),
                                'phone_number' => lang('General.phone'),
                                'tel' => lang('General.phone')
                            ];

                            // Sammle Kontaktdaten
                            foreach ($formFields as $key => $value) {
                                $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));
                                if (isset($contactKeys[$normalizedKey]) && !empty($value)) {
                                    $label = $contactKeys[$normalizedKey];
                                    if (!isset($customerInfo[$label])) {
                                        $customerInfo[$label] = $value;
                                    }
                                }
                            }

                            // Sammle Adressinformationen
                            $addressKeys = [
                                'strasse' => lang('General.street'),
                                'street' => lang('General.street'),
                                'address_line_1' => lang('General.street'),
                                'hausnummer' => lang('General.houseNumber'),
                                'house_number' => lang('General.houseNumber'),
                                'nummer' => lang('General.houseNumber'),
                                'address_line_2' => lang('General.houseNumber'),
                            ];

                            foreach ($formFields as $key => $value) {
                                // Prüfe verschachtelte Adressfelder
                                if (is_array($value) && (strpos(strtolower($key), 'adresse') !== false || strpos(strtolower($key), 'address') !== false)) {
                                    foreach ($value as $subKey => $subValue) {
                                        $normalizedSubKey = str_replace([' ', '-'], '_', strtolower($subKey));
                                        if (isset($addressKeys[$normalizedSubKey]) && !empty($subValue)) {
                                            $label = $addressKeys[$normalizedSubKey];
                                            if (!isset($addressInfo[$label])) {
                                                $addressInfo[$label] = $subValue;
                                            }
                                        }
                                    }
                                }

                                // Prüfe direkte Adressfelder
                                $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));
                                if (isset($addressKeys[$normalizedKey]) && !empty($value) && !is_array($value)) {
                                    $label = $addressKeys[$normalizedKey];
                                    if (!isset($addressInfo[$label])) {
                                        $addressInfo[$label] = $value;
                                    }
                                }
                            }
                            ?>

                            <?php if (!empty($customerInfo)): ?>
                                <!-- Kundeninformationen prominent anzeigen -->
                                <div class="mb-3 pb-3 border-bottom">
                                    <h5 class="mb-2"><i class="bi bi-person-circle text-success"></i> <?= esc(lang('General.customerInfo')) ?></h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php foreach ($customerInfo as $label => $value): ?>
                                                <p class="mb-1">
                                                    <strong><?= esc($label) ?>:</strong>
                                                    <?php if ($label === lang('General.email')): ?>
                                                        <a href="mailto:<?= esc($value) ?>"><?= esc($value) ?></a>
                                                    <?php elseif ($label === lang('General.phone')): ?>
                                                        <a href="tel:<?= esc($value) ?>"><?= esc($value) ?></a>
                                                    <?php else: ?>
                                                        <?= esc($value) ?>
                                                    <?php endif; ?>
                                                </p>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php if (!empty($addressInfo)): ?>
                                                <p class="mb-1">
                                                    <?php foreach ($addressInfo as $label => $value): ?>
                                                        <strong><?= esc($label) ?>:</strong> <?= esc($value) ?><br>
                                                    <?php endforeach; ?>
                                                </p>
                                            <?php endif; ?>

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
                                                    <small><?= lang('Offers.purchased_on') ?>: <?= date('d.m.Y - H:i', strtotime($offer['purchased_at'])) ?><?= !empty(lang('Offers.time_suffix')) ? ' ' . lang('Offers.time_suffix') : '' ?></small>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => $isPurchased, 'wrapInCard' => false]) ?>
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

    // Statistik-Collapse Icon Toggle
    const statsCollapse = document.getElementById('statsCollapse');
    const statsHeader = document.querySelector('[data-bs-target="#statsCollapse"]');
    if (statsCollapse && statsHeader) {
        const chevronIcon = statsHeader.querySelector('.bi-chevron-down');

        statsCollapse.addEventListener('show.bs.collapse', () => {
            if (chevronIcon) {
                chevronIcon.style.transform = 'rotate(0deg)';
            }
        });

        statsCollapse.addEventListener('hide.bs.collapse', () => {
            if (chevronIcon) {
                chevronIcon.style.transform = 'rotate(-90deg)';
            }
        });

        // Initial state
        if (!statsCollapse.classList.contains('show') && chevronIcon) {
            chevronIcon.style.transform = 'rotate(-90deg)';
        }
    }
</script>

<?= $this->endSection() ?>
