<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<h1 class="mb-4"><?= esc(sprintf(lang('Dashboard.welcomeUser'), $user->contact_person ?? lang('Dashboard.user'))) ?></h1>


<p><?= lang('Dashboard.status') ?>:
    <?php if ($user->active): ?>
        <span class="badge bg-success"><?= lang('Dashboard.active') ?></span>
    <?php else: ?>
        <span class="badge bg-secondary"><?= lang('Dashboard.inactive') ?></span>
    <?php endif; ?>
</p>

<?php if (empty($bookings)): ?>
    <!-- Anleitung für neue Benutzer -->
    <div class="alert alert-primary mt-4" role="alert">
        <h4 class="alert-heading">
            <i class="bi bi-info-circle me-2"></i><?= esc(sprintf(lang('Dashboard.welcomeHeading'), siteconfig()->name)) ?>
        </h4>
        <p class="mb-3"><?= lang('Dashboard.welcomeText') ?></p>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-funnel text-primary me-2"></i><?= lang('Dashboard.step1Title') ?>
                        </h5>
                        <p class="card-text"><?= lang('Dashboard.step1Text') ?></p>
                        <a href="<?= site_url('filter') ?>" class="btn btn-primary">
                            <i class="bi bi-gear me-1"></i><?= lang('Dashboard.step1Button') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-success">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-inbox text-success me-2"></i><?= lang('Dashboard.step2Title') ?>
                        </h5>
                        <p class="card-text"><?= lang('Dashboard.step2Text') ?></p>
                        <a href="<?= site_url('offers') ?>" class="btn btn-success">
                            <i class="bi bi-search me-1"></i><?= lang('Dashboard.step2Button') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-3">
        <p class="mb-0">
            <i class="bi bi-lightbulb text-warning me-2"></i>
            <?= lang('Dashboard.welcomeTip') ?>
        </p>
    </div>
<?php endif; ?>

<!-- Hinweis auf automatische Käufe, falls gewünscht
<div class="alert alert-info mt-4 d-flex align-items-center gap-2" role="alert">
    <i class="bi bi-info-circle fs-4"></i>
    <div>
        <?= lang('Dashboard.automaticPurchaseInfo') ?>
        <a href="/profile" class="alert-link"><?= lang('Dashboard.profile') ?></a>
        <?= lang('Dashboard.automaticPurchaseOption') ?>
    </div>
</div>
-->

<h2 class="mt-5 mb-3"><?= lang('Dashboard.purchasedOffers') ?></h2>

<?php if (empty($purchasedOffers)): ?>
    <p class="text-muted"><?= lang('Dashboard.noOffers') ?></p>
<?php else: ?>
    <div class="list-group mb-5">
        <?php foreach ($purchasedOffers as $offer): ?>
            <div class="list-group-item p-3 mb-3 border rounded bg-success bg-opacity-10 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1 me-3">
                        <span class="title fw-bold d-block">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            <?= esc($offer['dynamic_title'] ?? $offer['title']) ?>
                        </span>
                        <small class="text-muted">
                            <?= date('d.m.Y - H:i', strtotime($offer['created_at'])) ?><?= !empty(lang('Offers.time_suffix')) ? ' ' . lang('Offers.time_suffix') : '' ?> ·
                            <span class="badge bg-secondary"><?= lang('Offers.order_number') ?> #<?= $offer['id'] ?></span>
                        </small>
                        <br>
                        <a data-bs-toggle="collapse" href="#details-<?= $offer['id'] ?>" role="button" aria-expanded="false" aria-controls="details-<?= $offer['id'] ?>" data-toggle-icon="#toggleIcon-<?= $offer['id'] ?>">
                            <i class="bi bi-chevron-right" id="toggleIcon-<?= $offer['id'] ?>"></i> <?= lang('Offers.showDetails') ?>
                        </a>
                    </div>

                    <div class="text-end" style="min-width: 150px;">
                        <div class="small">
                            <?= number_format($offer['price_paid'] ?? $offer['discounted_price'] ?? $offer['price'], 2) ?> <?= currency() ?>
                        </div>
                        <a href="<?= site_url('offers/' . $offer['id']) ?>" class="btn btn-primary btn-sm mt-2"><?= lang('Offers.detailsButton') ?></a>
                    </div>
                </div>

                <div class="collapse mt-3" id="details-<?= $offer['id'] ?>">
                    <div class="card card-body bg-light">
                        <?php
                        // Kundeninfos extrahieren (wie in mine.php)
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

<!-- Affiliate Link Section -->
<div class="mt-5">
    <h2 class="mb-3">
        <i class="bi bi-share-fill me-2"></i>Empfehlungsprogramm
    </h2>

    <div class="card mb-4">
        <div class="card-body">
            <p class="card-text">
                Empfehlen Sie <?= esc(siteconfig()->name) ?> an andere Dienstleister und verdienen Sie Geld!
                Teilen Sie Ihren persönlichen Link und erhalten Sie eine Provision für jeden neuen Kunden.
            </p>

            <div class="input-group mb-3">
                <input type="text"
                       class="form-control"
                       id="affiliateLink"
                       value="<?= esc($affiliateLink) ?>"
                       readonly>
                <button class="btn btn-primary"
                        type="button"
                        onclick="copyAffiliateLink()">
                    <i class="bi bi-clipboard me-1"></i>Link kopieren
                </button>
            </div>

            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                <strong>So funktioniert's:</strong> Teilen Sie Ihren Link mit anderen Dienstleistern.
                Wenn sich jemand über Ihren Link registriert und aktiv wird, erhalten Sie eine Provision.
            </div>
        </div>
    </div>

    <!-- Referral Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h5 class="card-title text-primary mb-0"><?= $referralStats['total'] ?? 0 ?></h5>
                    <small class="text-muted">Gesamt Empfehlungen</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h5 class="card-title text-warning mb-0"><?= $referralStats['pending'] ?? 0 ?></h5>
                    <small class="text-muted">Ausstehend</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 class="card-title text-success mb-0"><?= $referralStats['credited'] ?? 0 ?></h5>
                    <small class="text-muted">Gutgeschrieben</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h5 class="card-title text-info mb-0"><?= number_format($referralStats['total_earned'] ?? 0, 2) ?> <?= currency() ?></h5>
                    <small class="text-muted">Gesamt verdient</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Referrals Table -->
    <?php if (!empty($referrals)): ?>
        <h3 class="mb-3">Ihre Empfehlungen</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Empfohlener Benutzer</th>
                        <th>Registriert am</th>
                        <th>Status</th>
                        <th>Provision</th>
                        <th>Gutgeschrieben am</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referrals as $referral): ?>
                        <tr>
                            <td><?= esc($referral['referred_username'] ?? 'User #' . $referral['referred_id']) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($referral['created_at'])) ?></td>
                            <td>
                                <?php
                                $statusClass = 'secondary';
                                $statusText = 'Ausstehend';
                                if ($referral['status'] === 'credited') {
                                    $statusClass = 'success';
                                    $statusText = 'Gutgeschrieben';
                                } elseif ($referral['status'] === 'rejected') {
                                    $statusClass = 'danger';
                                    $statusText = 'Abgelehnt';
                                }
                                ?>
                                <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td>
                                <?php if (!empty($referral['commission_amount'])): ?>
                                    <?= number_format($referral['commission_amount'], 2) ?> <?= currency() ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($referral['credited_at'])): ?>
                                    <?= date('d.m.Y', strtotime($referral['credited_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    function copyAffiliateLink() {
        const linkInput = document.getElementById('affiliateLink');
        linkInput.select();
        linkInput.setSelectionRange(0, 99999); // For mobile devices

        // Copy to clipboard
        navigator.clipboard.writeText(linkInput.value).then(() => {
            // Show success feedback
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kopiert!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');

            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        }).catch(err => {
            console.error('Fehler beim Kopieren:', err);
            alert('Fehler beim Kopieren des Links. Bitte manuell kopieren.');
        });
    }

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
