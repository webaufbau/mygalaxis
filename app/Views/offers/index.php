<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Offers.title')) ?></h2>

<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Hinweis:</strong> Hier werden alle Branchen und Projekte angezeigt, die registriert wurden. Sie können einzelne oder mehrere Branchen auswählen, um die Anzeige zu filtern.
</div>

<form method="get" class="mb-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-funnel me-2"></i><strong>Filter</strong>
        </div>
        <div class="card-body">
            <!-- Branchen-Filter (Mehrfachauswahl als Buttons) -->
            <div class="mb-3">
                <label class="form-label fw-bold">Branchen filtern:</label>
                <?php if (empty($categoryTypes)): ?>
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Sie haben noch keine Branchen ausgewählt. Bitte gehen Sie zu
                        <a href="<?= site_url('filter') ?>" class="alert-link">Branchen/Regionen</a>,
                        um Ihre Branchen zu konfigurieren.
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
                        <i class="bi bi-info-circle me-1"></i>Sie sehen nur die Branchen, die Sie unter <a href="<?= site_url('filter') ?>">Branchen/Regionen</a> ausgewählt haben.
                    </small>
                <?php endif; ?>
            </div>

            <!-- Status-Filter -->
            <div class="mb-3">
                <label class="form-label fw-bold">Status:</label>
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
                    <i class="bi bi-x-circle me-1"></i>Filter zurücksetzen
                </a>
            </div>
        </div>
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
                                'strasse' => 'Straße',
                                'street' => 'Straße',
                                'address_line_1' => 'Straße',
                                'hausnummer' => 'Hausnummer',
                                'house_number' => 'Hausnummer',
                                'nummer' => 'Hausnummer',
                                'address_line_2' => 'Adresszusatz',
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
</script>

<?= $this->endSection() ?>
