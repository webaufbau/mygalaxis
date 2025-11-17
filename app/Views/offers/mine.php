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
                        <span class="title fw-bold d-block"><?= esc($offer['dynamic_title'] ?? $offer['title']) ?></span>
                        <small class="text-muted">
                            <?= lang('Offers.purchased_on') ?> <?= \CodeIgniter\I18n\Time::parse($offer['purchased_at'])->setTimezone(app_timezone())->format('d.m.Y - H:i') ?><?= !empty(lang('Offers.time_suffix')) ? ' ' . lang('Offers.time_suffix') : '' ?> ·
                            <span class="badge bg-secondary"><?= lang('Offers.order_number') ?> #<?= $offer['id'] ?></span>
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
                                        <?= number_format($offer['price'], 2) ?> <?= currency() ?>
                                    </span>
                                    <span><?= number_format($displayPrice, 2) ?> <?= currency() ?></span>
                                <?php else: ?>
                                    <?= number_format($displayPrice, 2) ?> <?= currency() ?>
                                <?php endif; ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="collapse mt-3" id="details-<?= $offer['id'] ?>">
                    <div class="card card-body bg-light">
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

                        <?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => true, 'wrapInCard' => false]) ?>
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
