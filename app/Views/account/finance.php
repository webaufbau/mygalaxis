<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Finance.title')) ?></h2>

<div class="row g-4 mb-5">
    <!-- LINKE SPALTE: Guthaben & Aufladung -->
    <div class="col-lg-6">
        <!-- Guthaben mit Aufschlüsselung -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <strong><i class="bi bi-wallet2 me-2"></i><?= esc(lang('Finance.currentBalance')) ?></strong>
            </div>
            <div class="card-body">
                <!-- Aktueller Saldo -->
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <span class="fs-5"><strong><?= esc(lang('Finance.balance')) ?>:</strong></span>
                    <span class="fs-3 fw-bold <?= $balance >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= number_format($balance, 2, ".", "'") ?> <?= currency() ?>
                    </span>
                </div>

                <!-- Aufschlüsselung -->
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-plus-circle me-1 text-primary"></i><?= esc(lang('Finance.deposits')) ?>:</span>
                        <span class="text-primary"><?= number_format($topups, 2, ".", "'") ?> <?= currency() ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-dash-circle me-1 text-danger"></i><?= esc(lang('Finance.expenses')) ?>:</span>
                        <span class="text-danger"><?= number_format($expenses, 2, ".", "'") ?> <?= currency() ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guthaben aufladen -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <strong><i class="bi bi-plus-circle me-2"></i><?= esc(lang('Finance.topupButton')) ?></strong>
            </div>
            <div class="card-body">
                <form action="<?= site_url('finance/topup') ?>" method="post" id="topupForm">
                    <?= csrf_field() ?>

                    <!-- Schnell-Auswahl Buttons -->
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= esc(lang('Finance.selectAmount')) ?>:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-success topup-quick-btn" data-amount="10">10 <?= currency() ?></button>
                            <button type="button" class="btn btn-outline-success topup-quick-btn" data-amount="20">20 <?= currency() ?></button>
                            <button type="button" class="btn btn-outline-success topup-quick-btn" data-amount="50">50 <?= currency() ?></button>
                            <button type="button" class="btn btn-outline-success topup-quick-btn" data-amount="100">100 <?= currency() ?></button>
                            <button type="button" class="btn btn-outline-success topup-quick-btn" data-amount="200">200 <?= currency() ?></button>
                            <button type="button" class="btn btn-outline-success topup-quick-btn" data-amount="500">500 <?= currency() ?></button>
                        </div>
                    </div>

                    <!-- Eigener Betrag -->
                    <div class="mb-3">
                        <label for="amount" class="form-label fw-bold"><?= esc(lang('Finance.orEnterCustomAmount')) ?>:</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="10" name="amount" id="amount" class="form-control" value="100" required>
                            <span class="input-group-text"><?= currency() ?></span>
                        </div>
                        <small class="text-muted"><?= esc(lang('Finance.minimumAmount')) ?>: 10 <?= currency() ?></small>
                    </div>

                    <!-- AGB Switch -->
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" name="accept_agb" id="accept_agb" class="form-check-input" role="switch" value="1" required>
                        <label for="accept_agb" class="form-check-label"><?= lang('General.acceptAGB') ?></label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-credit-card me-1"></i><?= esc(lang('Finance.topupNow')) ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Hinterlegte Karten (Multi-Card System) -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-warning text-dark">
                <strong><i class="bi bi-credit-card-2-front me-2"></i><?= esc(lang('Finance.savedCard')) ?></strong>
            </div>
            <div class="card-body">
                <?php
                // Filtere nur Saferpay-Karten
                $saferpayCards = [];
                if ($hasSavedCard) {
                    $saferpayCards = array_filter($userPaymentMethods, function($card) {
                        return $card['payment_method_code'] === 'saferpay';
                    });
                    $saferpayCards = array_values($saferpayCards); // Re-index
                }
                ?>

                <?php if (!empty($saferpayCards)): ?>
                    <?php
                    // Prüfe einmal, ob irgendeine Karte als Primary markiert ist
                    $hasPrimaryCard = false;
                    foreach ($saferpayCards as $c) {
                        if ($c['is_primary'] == 1) {
                            $hasPrimaryCard = true;
                            break;
                        }
                    }
                    ?>

                    <!-- Liste aller Saferpay-Karten -->
                    <?php foreach ($saferpayCards as $index => $card): ?>
                        <?php
                        // Wenn nur eine Karte oder keine Primary markiert: erste Karte ist Primary
                        if (count($saferpayCards) == 1 || !$hasPrimaryCard) {
                            $isPrimary = ($index == 0);
                        } else {
                            $isPrimary = ($card['is_primary'] == 1);
                        }

                        $isExpired = false;
                        if ($card['card_expiry']) {
                            $parts = explode('/', $card['card_expiry']);
                            if (count($parts) == 2) {
                                $expMonth = (int)$parts[0];
                                $expYear = (int)$parts[1];
                                $expDate = new DateTime("$expYear-$expMonth-01");
                                $expDate->modify('last day of this month');
                                $isExpired = $expDate < new DateTime();
                            }
                        }

                        // Kartendetails aus provider_data holen falls Felder leer sind
                        $cardBrand = $card['card_brand'];
                        $cardLast4 = $card['card_last4'];
                        if (empty($cardBrand) || empty($cardLast4)) {
                            $providerData = !empty($card['provider_data']) ? json_decode($card['provider_data'], true) : [];
                            if (empty($cardBrand)) {
                                $cardBrand = $providerData['card_brand'] ?? 'Kreditkarte';
                            }
                            if (empty($cardLast4) && !empty($providerData['card_masked'])) {
                                // Extrahiere Last 4 aus card_masked (z.B. "9000 xxxx xxxx 0006")
                                if (preg_match('/(\d{4})\s*$/', $providerData['card_masked'], $matches)) {
                                    $cardLast4 = $matches[1];
                                }
                            }
                        }
                        ?>
                        <div class="alert <?= $isExpired ? 'alert-danger' : ($isPrimary ? 'alert-success' : 'alert-secondary') ?> mb-2 d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <?php if ($isPrimary && !$isExpired): ?>
                                    <span class="badge bg-warning text-dark me-2">⭐ <?= esc(lang('Finance.badgePrimary')) ?></span>
                                <?php endif; ?>
                                <?php if ($isExpired): ?>
                                    <span class="badge bg-danger me-2">⚠️ Abgelaufen</span>
                                <?php endif; ?>
                                <strong><?= esc($cardBrand) ?></strong>
                                <?php if ($cardLast4): ?>
                                    •••• <?= esc($cardLast4) ?>
                                <?php endif; ?>
                                <?php if ($card['card_expiry']): ?>
                                    <small class="<?= $isExpired ? 'text-white' : 'text-muted' ?> ms-2">(<?= esc($card['card_expiry']) ?>)</small>
                                <?php endif; ?>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <?php if (!$isExpired && !$isPrimary && count($saferpayCards) > 1): ?>
                                    <a href="<?= site_url('finance/set-primary-card/' . $card['id']) ?>"
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-star"></i> <?= esc(lang('Finance.buttonSetPrimary')) ?>
                                    </a>
                                <?php endif; ?>
                                <?php if (count($saferpayCards) > 1 || !$isPrimary || $isExpired): ?>
                                    <a href="<?= site_url('finance/register-payment-method?replace=' . $card['id']) ?>"
                                       class="btn btn-warning btn-sm text-dark">
                                        <i class="bi bi-arrow-repeat"></i> <?= esc(lang('Finance.buttonReplace')) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Weitere Karte hinzufügen (max 2) -->
                    <?php if (count($saferpayCards) < 2): ?>
                        <div class="d-grid gap-2 mt-3">
                            <a href="<?= site_url('finance/register-payment-method') ?>" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Weitere Karte hinzufügen
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-light mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>
                            <?= lang('Finance.fallbackLogicInfo') ?>
                        </small>
                    </div>
                <?php else: ?>
                    <!-- Noch keine Saferpay-Karte -->
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong><?= esc(lang('Finance.noCardRegistered')) ?></strong>
                    </div>
                    <p class="mb-3">
                        <?= esc(lang('Finance.registerPaymentMethodInfo')) ?>
                    </p>
                    <div class="d-grid gap-2">
                        <a href="<?= site_url('finance/register-payment-method') ?>" class="btn btn-primary">
                            <i class="bi bi-credit-card me-1"></i><?= esc(lang('Finance.registerPaymentMethod')) ?>
                        </a>
                    </div>
                    <div class="alert alert-light mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>
                            <?= lang('Finance.availablePaymentMethods') ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- RECHTE SPALTE: Sofortkauf-Info & Einstellungen -->
    <div class="col-lg-6">
        <!-- Sofortkauf Info -->
        <div class="card mb-4 shadow-sm border-info">
            <div class="card-header bg-info text-white">
                <strong><i class="bi bi-lightning-charge me-2"></i><?= esc(lang('Finance.instantAutoPurchase')) ?></strong>
            </div>
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    <?= esc(lang('Finance.getRequestsInstantly')) ?>
                </h6>

                <p class="mb-3">
                    <?= lang('Finance.instantPurchaseDescription') ?>
                </p>

                <div class="alert alert-warning mb-3">
                    <small>
                        <i class="bi bi-people-fill me-1"></i>
                        <?= lang('Finance.maxCompaniesNote') ?>
                    </small>
                </div>

                <h6 class="fw-bold mb-2"><?= esc(lang('Finance.howItWorks')) ?>:</h6>
                <ul class="small mb-3">
                    <li class="mb-2">
                        <i class="bi bi-1-circle text-primary me-1"></i>
                        <?= lang('Finance.step1') ?>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-2-circle text-primary me-1"></i>
                        <?= esc(lang('Finance.step2')) ?>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-3-circle text-primary me-1"></i>
                        <?= sprintf(lang('Finance.step3'), site_url('agenda')) ?>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-4-circle text-primary me-1"></i>
                        <?= lang('Finance.step4') ?>
                    </li>
                </ul>

                <div class="alert alert-info mb-3">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        <?= lang('Finance.priorityNote') ?>
                    </small>
                </div>

                <div class="alert alert-light mb-0">
                    <i class="bi bi-shield-check text-success me-2"></i>
                    <small>
                        <?= lang('Finance.paymentMethodNote') ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Einstellungen -->
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <strong><i class="bi bi-gear me-2"></i><?= esc(lang('Finance.purchaseSettings')) ?></strong>
            </div>
            <div class="card-body">
                <form action="<?= site_url('finance/update-settings') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="form-check form-switch mb-3">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="auto_purchase"
                            name="auto_purchase"
                            value="1"
                            <?= !empty($user->auto_purchase) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label" for="auto_purchase">
                            <strong><?= esc(lang('Finance.enableAutoPurchase')) ?></strong>
                        </label>
                    </div>

                    <?php if (!empty($user->auto_purchase) && !empty($user->auto_purchase_activated_at)): ?>
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-calendar-check me-1"></i>
                            <small>
                                <strong><?= esc(lang('Finance.activatedSince')) ?>:</strong>
                                <?= date('d.m.Y H:i', strtotime($user->auto_purchase_activated_at)) ?> Uhr
                            </small>
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                <?= esc(lang('Finance.queueInfoShort')) ?>
                            </small>
                        </div>
                    <?php elseif (!empty($user->auto_purchase)): ?>
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <small>
                                <?= lang('Finance.activationDateMissing') ?>
                            </small>
                        </div>
                    <?php endif; ?>

                    <p class="small text-muted mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        <?= esc(lang('Finance.autoPurchaseInfo')) ?>
                    </p>

                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-check-lg me-1"></i><?= esc(lang('Finance.saveSettings')) ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Schnell-Buttons Logik
document.querySelectorAll('.topup-quick-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const amount = this.getAttribute('data-amount');
        document.getElementById('amount').value = amount;

        // Visuelles Feedback
        document.querySelectorAll('.topup-quick-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>


<!-- Filter -->
<form method="get" class="mb-4 d-flex align-items-end flex-wrap" style="gap: 1rem;">
    <!-- Jahr -->
    <div>
        <label for="year" class="form-label mb-0 me-2"><?= esc(lang('Finance.year')) ?>:</label>
        <select id="year" name="year" class="form-select form-select-sm" onchange="this.form.submit();">
            <option value=""><?= esc(lang('Finance.allYears')) ?></option>
            <?php foreach ($years as $y): ?>
                <option value="<?= $y['year'] ?>" <?= $currentYear == $y['year'] ? 'selected' : '' ?>>
                    <?= $y['year'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Monat -->
    <div>
        <label for="month" class="form-label mb-0 me-2"><?= esc(lang('Finance.month')) ?>:</label>
        <select id="month" name="month" class="form-select form-select-sm" onchange="this.form.submit();">
            <option value=""><?= esc(lang('Calendar.allMonths')) ?></option>
            <?php
            $months = lang('Calendar.monthNames');
            for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $currentMonth == $m ? 'selected' : '' ?>>
                    <?= esc($months[$m]) ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>

    <!-- Filter anwenden -->
    <button type="submit" class="btn btn-sm btn-primary"><?= esc(lang('Finance.showButton')) ?></button>

    <!-- PDF-Export -->
    <a href="<?= site_url('finance/pdf?year=' . ($currentYear ?? '') . '&month=' . ($currentMonth ?? '')) ?>"
       class="btn btn-sm btn-secondary">
        <i class="bi bi-file-earmark-pdf"></i> <?= esc(lang('Finance.pdfExport')) ?>
    </a>

    <!-- Monatrechnung -->
    <?php if (!empty($currentMonth) && !empty($currentYear)): ?>
        <a href="<?= site_url('finance/monthly-invoice/' . $currentYear . '/' . $currentMonth) ?>"
           class="btn btn-sm btn-success">
            <i class="bi bi-file-earmark-text"></i> <?= esc(lang('Finance.monthlyInvoice')) ?>
        </a>
    <?php endif; ?>
</form>

<!-- SECTION 1: Gekaufte Anfragen -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-basket me-2"></i><?= esc(lang('Finance.purchasedRequests')) ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($purchases)): ?>
            <div class="alert alert-info mb-0"><?= esc(lang('Finance.noPurchasesInPeriod')) ?></div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="purchases-table" class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th><?= esc(lang('Finance.date')) ?></th>
                        <th><?= esc(lang('Finance.type')) ?></th>
                        <th><?= esc(lang('Finance.description')) ?></th>
                        <th class="text-end"><?= esc(lang('Finance.cardPayment')) ?></th>
                        <th class="text-end"><?= esc(lang('Finance.balanceChange')) ?></th>
                        <th><?= esc(lang('Finance.invoice')) ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($purchases as $entry): ?>
                        <tr>
                            <td data-order="<?= strtotime($entry['created_at']) ?>"><?= date('d.m.Y H:i', strtotime($entry['created_at'])) ?></td>
                            <td><?= esc(lang('Offers.credit_type.'.$entry['type'])) ?></td>
                            <td><?= esc($entry['description']) ?></td>
                            <td class="text-end">
                                <?php if (!empty($entry['paid_amount']) && $entry['paid_amount'] > 0): ?>
                                    <span class="text-primary">
                                        <?= number_format($entry['paid_amount'], 2, ".", "'") ?> <?= currency() ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($entry['amount'] != 0): ?>
                                    <span class="text-danger">
                                        <?= number_format($entry['amount'], 2, ".", "'") ?> <?= currency() ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= site_url('finance/invoice/'.$entry['id']) ?>"
                                   class="btn btn-sm btn-secondary">
                                    <i class="bi bi-file-earmark-pdf"></i> RE<?=strtoupper(siteconfig()->siteCountry);?><?=$entry['id'];?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- SECTION 2: Gutschriftenübersicht -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i><?= esc(lang('Finance.creditsOverview')) ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($credits)): ?>
            <div class="alert alert-info mb-0"><?= esc(lang('Finance.noCreditsInPeriod')) ?></div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="credits-table" class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th><?= esc(lang('Finance.receipt')) ?></th>
                        <th><?= esc(lang('Finance.date')) ?></th>
                        <th class="text-end"><?= esc(lang('Finance.credit')) ?></th>
                        <th class="text-end"><?= esc(lang('Finance.balance')) ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($credits as $entry): ?>
                        <tr>
                            <td>
                                <?php if ($entry['amount'] > 0): ?>
                                    <span class="text-success">
                                        <i class="bi bi-plus-circle-fill me-1"></i>
                                        <?php
                                        // Prüfe ob es eine Weiterempfehlungs-Gutschrift ist
                                        if (stripos($entry['description'], 'Weiterempfehlungs-Gutschrift') !== false) {
                                            echo '<i class="bi bi-people-fill me-1"></i>';
                                            echo esc($entry['description']);
                                        } else {
                                            echo esc(lang('Finance.topup'));
                                        }
                                        ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-secondary">
                                        <i class="bi bi-dash-circle-fill me-1"></i>
                                        <?= esc(lang('Finance.deduction')) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td data-order="<?= strtotime($entry['created_at']) ?>"><?= date('d.m.Y H:i', strtotime($entry['created_at'])) ?> <?= esc(lang('Finance.clock')) ?></td>
                            <td class="text-end">
                                <span class="<?= $entry['amount'] > 0 ? 'text-success' : 'text-secondary' ?>">
                                    <?= $entry['amount'] > 0 ? '+' : '' ?><?= number_format($entry['amount'], 2, ".", "'") ?> <?= currency() ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <strong class="<?= $entry['running_balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($entry['running_balance'], 2, ".", "'") ?> <?= currency() ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- SECTION 3: Ausgestellte Monatsrechnungen -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i><?= esc(lang('Finance.issuedMonthlyInvoices')) ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($monthlyInvoices)): ?>
            <div class="alert alert-info mb-0"><?= esc(lang('Finance.noMonthlyInvoices')) ?></div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="monthly-invoices-table" class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th><?= esc(lang('Finance.receipt')) ?></th>
                        <th><?= esc(lang('Finance.invoiceType')) ?></th>
                        <th><?= esc(lang('Finance.period')) ?></th>
                        <th><?= esc(lang('Finance.issuedOn')) ?></th>
                        <th class="text-end"><?= esc(lang('Finance.grossAmountInclVat')) ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($monthlyInvoices as $invoice): ?>
                        <tr>
                            <td>
                                <a href="<?= site_url('finance/monthly-invoice-pdf/'.$invoice['period']) ?>"
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-file-earmark-pdf"></i> <?= esc($invoice['invoice_number']) ?>
                                </a>
                            </td>
                            <td><?= esc(lang('Finance.monthlyInvoice')) ?></td>
                            <td>
                                <?php
                                $periodDate = DateTime::createFromFormat('Y-m', $invoice['period']);
                                echo $periodDate ? $periodDate->format('m/Y') : $invoice['period'];
                                ?>
                            </td>
                            <td><?= date('d.m.Y', strtotime($invoice['created_at'])) ?></td>
                            <td class="text-end">
                                <strong><?= number_format($invoice['amount'], 2, ".", "'") ?> <?= currency() ?></strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // DataTables Sprache basierend auf aktueller Benutzersprache
    <?php
    $locale = service('request')->getLocale();
    $dtLanguageMap = [
        'de' => 'de-DE',
        'en' => 'en-GB',
        'fr' => 'fr-FR',
        'it' => 'it-IT'
    ];
    $dtLanguage = $dtLanguageMap[$locale] ?? 'de-DE';
    ?>
    const dtLanguageUrl = 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/<?= $dtLanguage ?>.json';

    // Gekaufte Anfragen Tabelle
    <?php if (!empty($purchases)): ?>
    $('#purchases-table').DataTable({
        order: [[0, 'asc']], // Nach Datum aufsteigend sortieren (neuestes unten)
        pageLength: 25,
        language: {
            url: dtLanguageUrl
        },
        columnDefs: [
            { orderable: false, targets: [5] } // Rechnung-Spalte nicht sortierbar
        ]
    });
    <?php endif; ?>

    // Gutschriftenübersicht Tabelle
    <?php if (!empty($credits)): ?>
    $('#credits-table').DataTable({
        order: [[1, 'asc']], // Nach Datum aufsteigend sortieren (neuestes unten)
        pageLength: 25,
        language: {
            url: dtLanguageUrl
        }
    });
    <?php endif; ?>

    // Monatsrechnungen Tabelle
    <?php if (!empty($monthlyInvoices)): ?>
    $('#monthly-invoices-table').DataTable({
        order: [[2, 'desc']], // Nach Periode absteigend sortieren
        pageLength: 10,
        language: {
            url: dtLanguageUrl
        },
        columnDefs: [
            { orderable: false, targets: [0] } // Beleg-Spalte nicht sortierbar
        ]
    });
    <?php endif; ?>

    // Referrals Table
    <?php if (!empty($userReferrals)): ?>
    $('#referrals-table').DataTable({
        order: [[3, 'desc']], // Nach Datum absteigend sortieren
        pageLength: 10,
        language: {
            url: dtLanguageUrl
        }
    });
    <?php endif; ?>
});
</script>

<!-- SECTION 4: Weiterempfehlungen & Guthaben -->
<div class="card mb-4 shadow-sm border-success">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i><?= esc(lang('Finance.referralAndCredit')) ?></h5>
    </div>
    <div class="card-body">
        <div class="alert alert-success">
            <h5><i class="bi bi-gift me-2"></i><?= esc(lang('Finance.referralReward')) ?></h5>
            <p class="mb-0"><?= esc(lang('Finance.referralInfo')) ?></p>
        </div>

        <!-- Affiliate Link -->
        <?php if (!empty($affiliateLink)): ?>
        <div class="card mb-4 bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-link-45deg me-2"></i><?= esc(lang('Finance.yourAffiliateLink')) ?>:</h6>
                <div class="input-group">
                    <input type="text" class="form-control" id="affiliateLink" value="<?= esc($affiliateLink) ?>" readonly>
                    <button class="btn btn-primary" type="button" onclick="copyAffiliateLink()">
                        <i class="bi bi-clipboard"></i> <?= esc(lang('Finance.copy')) ?>
                    </button>
                </div>
                <small class="form-text text-muted mt-2 d-block">
                    <?= esc(lang('Finance.affiliateCode')) ?>: <strong><?= esc($affiliateCode) ?></strong>
                </small>
            </div>
        </div>

        <script>
        function copyAffiliateLink() {
            var copyText = document.getElementById("affiliateLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);

            // Visual feedback
            var btn = event.target.closest('button');
            var originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> <?= esc(lang('Finance.copied')) ?>!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');

            setTimeout(function() {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        }
        </script>
        <?php endif; ?>

        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2 class="text-primary"><?= $referralStats['total'] ?></h2>
                        <p class="text-muted mb-0"><?= esc(lang('Finance.totalReferrals')) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2 class="text-warning"><?= $referralStats['pending'] ?></h2>
                        <p class="text-muted mb-0"><?= esc(lang('Finance.pending')) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2 class="text-success"><?= $referralStats['credited'] ?></h2>
                        <p class="text-muted mb-0"><?= esc(lang('Finance.credited')) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h2><?= number_format($referralStats['total_earned'], 2, '.', "'") ?> CHF</h2>
                        <p class="mb-0"><?= esc(lang('Finance.earned')) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referrals Tabelle -->
        <?php if (empty($userReferrals)): ?>
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i><?= esc(lang('Finance.noReferralsYet')) ?>
            </div>
        <?php else: ?>
            <h6 class="mb-3"><i class="bi bi-list-ul me-2"></i><?= esc(lang('Finance.yourReferrals')) ?>:</h6>
            <div class="table-responsive">
                <table id="referrals-table" class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?= esc(lang('Finance.company')) ?></th>
                            <th><?= esc(lang('Finance.email')) ?></th>
                            <th><?= esc(lang('Finance.status')) ?></th>
                            <th><?= esc(lang('Finance.date')) ?></th>
                            <th class="text-end"><?= esc(lang('Finance.credit')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userReferrals as $referral): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($referral['referred_company_name'])): ?>
                                        <strong><?= esc($referral['referred_company_name']) ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($referral['referred_email']) ?></td>
                                <td>
                                    <?php
                                    $statusBadge = match($referral['status']) {
                                        'pending' => 'bg-warning text-dark',
                                        'credited' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    $statusText = match($referral['status']) {
                                        'pending' => lang('Finance.statusPending'),
                                        'credited' => lang('Finance.statusCredited'),
                                        'rejected' => lang('Finance.statusRejected'),
                                        default => $referral['status']
                                    };
                                    ?>
                                    <span class="badge <?= $statusBadge ?>">
                                        <?= esc($statusText) ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?= date('d.m.Y H:i', strtotime($referral['created_at'])) ?></small>
                                </td>
                                <td class="text-end">
                                    <?php if ($referral['status'] === 'credited'): ?>
                                        <strong class="text-success">
                                            <?= number_format($referral['credit_amount'], 2, '.', "'") ?> CHF
                                        </strong>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <?= number_format($referral['credit_amount'], 2, '.', "'") ?> CHF
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
