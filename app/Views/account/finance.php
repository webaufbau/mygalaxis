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
                    <span class="fs-5"><strong>Saldo:</strong></span>
                    <span class="fs-3 fw-bold <?= $balance >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= number_format($balance, 2, ".", "'") ?> <?= currency() ?>
                    </span>
                </div>

                <!-- Aufschlüsselung -->
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-plus-circle me-1 text-primary"></i>Einzahlungen:</span>
                        <span class="text-primary"><?= number_format($topups, 2, ".", "'") ?> <?= currency() ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-dash-circle me-1 text-danger"></i>Ausgaben:</span>
                        <span class="text-danger"><?= number_format($expenses, 2, ".", "'") ?> <?= currency() ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guthaben aufladen -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <strong><i class="bi bi-plus-circle me-2"></i>Guthaben aufladen</strong>
            </div>
            <div class="card-body">
                <form action="<?= site_url('finance/topup') ?>" method="post" id="topupForm">
                    <?= csrf_field() ?>

                    <!-- Schnell-Auswahl Buttons -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Betrag wählen:</label>
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
                        <label for="amount" class="form-label fw-bold">Oder eigenen Betrag eingeben:</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="10" name="amount" id="amount" class="form-control" value="100" required>
                            <span class="input-group-text"><?= currency() ?></span>
                        </div>
                        <small class="text-muted">Mindestbetrag: 10 <?= currency() ?></small>
                    </div>

                    <!-- AGB Switch -->
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" name="accept_agb" id="accept_agb" class="form-check-input" role="switch" value="1" required>
                        <label for="accept_agb" class="form-check-label"><?= lang('General.acceptAGB') ?></label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-credit-card me-1"></i>Jetzt aufladen
                    </button>
                </form>
            </div>
        </div>

        <!-- Hinterlegte Karte -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-warning text-dark">
                <strong><i class="bi bi-credit-card-2-front me-2"></i>Hinterlegte Karte</strong>
            </div>
            <div class="card-body">
                <?php if ($hasSavedCard): ?>
                    <!-- Gespeicherte Karte anzeigen -->
                    <?php $savedCard = $userPaymentMethods[0]; ?>
                    <div class="alert alert-success mb-3">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>
                            <?php if (!empty($cardBrand)): ?>
                                <?= esc($cardBrand) ?> hinterlegt
                            <?php else: ?>
                                Kreditkarte hinterlegt
                            <?php endif; ?>
                        </strong>
                    </div>
                    <p class="mb-3">
                        <i class="bi bi-shield-check text-success me-2"></i>
                        <?php if (!empty($cardBrand)): ?>
                            Ihr Zahlungsmittel (<?= esc($cardBrand) ?>) ist sicher bei <strong>Worldline (Saferpay)</strong> gespeichert.
                        <?php else: ?>
                            Ihre Kreditkarte ist sicher bei <strong>Worldline (Saferpay)</strong> gespeichert.
                        <?php endif; ?>
                    </p>
                    <div class="d-grid gap-2">
                        <a href="<?= site_url('finance/register-payment-method') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>Zahlungsmittel ändern
                        </a>
                    </div>

                    <div class="alert alert-light mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>
                            Wählen Sie zwischen <strong>Kreditkarte oder TWINT</strong>.
                            Beim Ändern wird die alte Zahlungsmethode ersetzt. Es erfolgt keine Belastung.
                        </small>
                    </div>
                <?php else: ?>
                    <!-- Noch keine Karte -->
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Noch keine Karte hinterlegt</strong>
                    </div>
                    <p class="mb-3">
                        Hinterlegen Sie ein Zahlungsmittel, um den automatischen Kauf zu nutzen
                        oder Guthaben aufzuladen.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="<?= site_url('finance/register-payment-method') ?>" class="btn btn-primary">
                            <i class="bi bi-credit-card me-1"></i>Zahlungsmittel hinterlegen
                        </a>
                    </div>
                    <div class="alert alert-light mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>
                            <strong>Verfügbar:</strong> Kreditkarte (Visa, Mastercard) oder TWINT.
                            Sie können auch beim ersten Guthaben-Aufladen ein Zahlungsmittel hinterlegen.
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
                <strong><i class="bi bi-lightning-charge me-2"></i>Sofortkauf & Automatischer Kauf</strong>
            </div>
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    Anfragen sofort erhalten – ohne manuellen Kauf!
                </h6>

                <p class="mb-3">
                    Beim <strong>Sofortkauf</strong> erhalten Sie passende Anfragen <strong>sofort nach dem Eingang im System</strong>,
                    ohne diese vorher anschauen zu müssen. Somit sichern Sie sich zu <strong>100% die Anfragen</strong> –
                    auch wenn Sie unterwegs oder anderweitig beschäftigt sind.
                </p>

                <div class="alert alert-warning mb-3">
                    <small>
                        <i class="bi bi-people-fill me-1"></i>
                        <strong>Hinweis:</strong> Pro Anfrage können maximal 3 Firmen kaufen.
                        Wenn Sie den automatischen Kauf aktivieren, werden Sie nach dem <strong>Aktivierungsdatum</strong> berücksichtigt.
                        Die ersten 3 Firmen (nach Aktivierungsdatum sortiert) mit gültigem Zahlungsmittel erhalten die Anfrage automatisch.
                    </small>
                </div>

                <h6 class="fw-bold mb-2">So funktioniert's:</h6>
                <ul class="small mb-3">
                    <li class="mb-2">
                        <i class="bi bi-1-circle text-primary me-1"></i>
                        <strong>Aktivieren Sie unten</strong> den automatischen Kauf
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-2-circle text-primary me-1"></i>
                        System prüft: Guthaben vorhanden ODER hinterlegte Karte verfügbar
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-3-circle text-primary me-1"></i>
                        System prüft: Sind Sie an diesem Tag über <a href="<?= site_url('agenda') ?>">Agenda</a> blockiert?
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-4-circle text-primary me-1"></i>
                        Wenn alles OK: <strong>Anfrage wird automatisch gekauft</strong>
                    </li>
                </ul>

                <div class="alert alert-info mb-3">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Priorität:</strong> Falls Sie kein Guthaben oder keine Karte hinterlegt haben,
                        oder an dem Tag blockiert sind, erhält automatisch die nächste Firma in der Warteschlange den Zuschlag.
                    </small>
                </div>

                <div class="alert alert-light mb-0">
                    <i class="bi bi-shield-check text-success me-2"></i>
                    <small>
                        <strong>Zahlungsmethode:</strong> Beim ersten Kauf werden Ihre Kartendaten sicher bei
                        <strong>Worldline (Saferpay)</strong> gespeichert. Danach erfolgen alle automatischen Käufe
                        von Ihrem Guthaben oder Ihrer gespeicherten Karte.
                    </small>
                </div>
            </div>
        </div>

        <!-- Einstellungen -->
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <strong><i class="bi bi-gear me-2"></i>Kaufeinstellungen</strong>
            </div>
            <div class="card-body">
                <form action="<?= site_url('profile/update') ?>" method="post">
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
                            <strong>Automatischer Kauf aktivieren</strong>
                        </label>
                    </div>

                    <?php if (!empty($user->auto_purchase) && !empty($user->auto_purchase_activated_at)): ?>
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-calendar-check me-1"></i>
                            <small>
                                <strong>Aktiviert seit:</strong>
                                <?= date('d.m.Y H:i', strtotime($user->auto_purchase_activated_at)) ?> Uhr
                            </small>
                            <br>
                            <small class="text-muted">
                                Sie befinden sich in der Warteschlange für automatische Käufe.
                                Die Priorität wird nach diesem Datum bestimmt.
                            </small>
                        </div>
                    <?php endif; ?>

                    <p class="small text-muted mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Wenn aktiviert, werden passende Anfragen automatisch für Sie gekauft.
                        Die Bezahlung erfolgt entweder von Ihrem Guthaben oder direkt von Ihrer gespeicherten Karte.
                    </p>

                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-check-lg me-1"></i>Einstellungen speichern
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
            <i class="bi bi-file-earmark-text"></i> Monatsrechnung
        </a>
    <?php endif; ?>
</form>

<!-- Tabelle -->
<?php if (empty($bookings)): ?>
    <div class="alert alert-warning"><?= esc(lang('Finance.noBookings')) ?></div>
<?php else: ?>

    <div class="table-responsive" style="overflow-y: auto;">
        <table id="finance-transactions-table" class="table table-bordered table-hover align-middle mb-0">
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
            <?php foreach ($bookings as $entry): ?>
                <tr>
                    <td><?= date('d.m.Y', strtotime($entry['created_at'])) ?></td>
                    <td><?= esc(lang('Offers.credit_type.'.$entry['type'])) ?></td>
                    <td><?= esc($entry['description']) ?></td>
                    <!-- Kartenzahlung Spalte -->
                    <td class="text-end">
                        <?php if (!empty($entry['paid_amount']) && $entry['paid_amount'] > 0): ?>
                            <span class="text-primary">
                                <?= number_format($entry['paid_amount'], 2, ".", "'") ?> <?= currency() ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <!-- Guthaben-Änderung Spalte -->
                    <td class="text-end">
                        <?php if ($entry['amount'] != 0): ?>
                            <span class="<?= $entry['amount'] < 0 ? 'text-danger' : 'text-success' ?>">
                                <?= number_format($entry['amount'], 2, ".", "'") ?> <?= currency() ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">0.00 <?= currency() ?></span>
                        <?php endif; ?>
                    </td>
                    <!-- Rechnung -->
                    <td>
                        <?php if ($entry['type'] === 'offer_purchase'): ?>
                            <a href="<?= site_url('finance/invoice/'.$entry['id']) ?>"
                               class="btn btn-sm btn-secondary">
                                <i class="bi bi-file-earmark-pdf"></i> RE<?=strtoupper(siteconfig()->siteCountry);?><?=$entry['id'];?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    <?php if (!empty($bookings)): ?>
    $('#finance-transactions-table').DataTable({
        order: [[0, 'desc']], // Nach Datum absteigend sortieren
        pageLength: 25,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json'
        },
        columnDefs: [
            { orderable: false, targets: [5] } // Rechnung-Spalte nicht sortierbar
        ]
    });
    <?php endif; ?>
});
</script>

<?= $this->endSection() ?>
