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

<!-- SECTION 1: Gekaufte Anfragen -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-basket me-2"></i>Gekaufte Anfragen</h5>
    </div>
    <div class="card-body">
        <?php if (empty($purchases)): ?>
            <div class="alert alert-info mb-0">Keine Käufe im gewählten Zeitraum.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="purchases-table" class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Datum</th>
                        <th>Typ</th>
                        <th>Beschreibung</th>
                        <th class="text-end">Kartenzahlung</th>
                        <th class="text-end">Guthaben-Änderung</th>
                        <th>Rechnung</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($purchases as $entry): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($entry['created_at'])) ?></td>
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
        <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Gutschriftenübersicht</h5>
    </div>
    <div class="card-body">
        <?php if (empty($credits)): ?>
            <div class="alert alert-info mb-0">Keine Gutschriften im gewählten Zeitraum.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="credits-table" class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Beleg</th>
                        <th>Datum</th>
                        <th class="text-end">Guthaben</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($credits as $entry): ?>
                        <tr>
                            <td>
                                <?php if ($entry['amount'] > 0): ?>
                                    <span class="text-success">
                                        <i class="bi bi-plus-circle-fill me-1"></i>
                                        Aufladung
                                    </span>
                                <?php else: ?>
                                    <span class="text-secondary">
                                        <i class="bi bi-dash-circle-fill me-1"></i>
                                        Abzug
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($entry['created_at'])) ?> Uhr</td>
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
        <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Ausgestellte Monatsrechnungen</h5>
    </div>
    <div class="card-body">
        <?php if (empty($monthlyInvoices)): ?>
            <div class="alert alert-info mb-0">Keine Monatsrechnungen vorhanden.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="monthly-invoices-table" class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Beleg</th>
                        <th>Art</th>
                        <th>Periode</th>
                        <th>Ausgestellt am</th>
                        <th class="text-end">Brutto Betrag inkl. MWST</th>
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
                            <td>Monatsrechnung</td>
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
    // Gekaufte Anfragen Tabelle
    <?php if (!empty($purchases)): ?>
    $('#purchases-table').DataTable({
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

    // Gutschriftenübersicht Tabelle
    <?php if (!empty($credits)): ?>
    $('#credits-table').DataTable({
        order: [[1, 'desc']], // Nach Datum absteigend sortieren
        pageLength: 25,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json'
        }
    });
    <?php endif; ?>

    // Monatsrechnungen Tabelle
    <?php if (!empty($monthlyInvoices)): ?>
    $('#monthly-invoices-table').DataTable({
        order: [[2, 'desc']], // Nach Periode absteigend sortieren
        pageLength: 10,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json'
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
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json'
        }
    });
    <?php endif; ?>
});
</script>

<!-- SECTION 4: Weiterempfehlungen & Guthaben -->
<div class="card mb-4 shadow-sm border-success">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Weiterempfehlung & Guthaben erhalten</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-success">
            <h5><i class="bi bi-gift me-2"></i>Für jede erfolgreich vermittelte Firma erhalten Sie 50 CHF Gutschrift!</h5>
            <p class="mb-0">Teilen Sie Ihren persönlichen Affiliate-Link und erhalten Sie eine Gutschrift, sobald sich eine neue Firma über Ihren Link registriert und vom Admin genehmigt wird.</p>
        </div>

        <!-- Affiliate Link -->
        <?php if (!empty($affiliateLink)): ?>
        <div class="card mb-4 bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-link-45deg me-2"></i>Ihr persönlicher Affiliate-Link:</h6>
                <div class="input-group">
                    <input type="text" class="form-control" id="affiliateLink" value="<?= esc($affiliateLink) ?>" readonly>
                    <button class="btn btn-primary" type="button" onclick="copyAffiliateLink()">
                        <i class="bi bi-clipboard"></i> Kopieren
                    </button>
                </div>
                <small class="form-text text-muted mt-2 d-block">
                    Affiliate-Code: <strong><?= esc($affiliateCode) ?></strong>
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
            btn.innerHTML = '<i class="bi bi-check"></i> Kopiert!';
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
                        <p class="text-muted mb-0">Total Vermittlungen</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2 class="text-warning"><?= $referralStats['pending'] ?></h2>
                        <p class="text-muted mb-0">Ausstehend</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2 class="text-success"><?= $referralStats['credited'] ?></h2>
                        <p class="text-muted mb-0">Gutgeschrieben</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h2><?= number_format($referralStats['total_earned'], 2, '.', "'") ?> CHF</h2>
                        <p class="mb-0">Verdient</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referrals Tabelle -->
        <?php if (empty($userReferrals)): ?>
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>Sie haben noch keine Firmen weitervermittelt. Teilen Sie Ihren Affiliate-Link, um Gutschriften zu erhalten!
            </div>
        <?php else: ?>
            <h6 class="mb-3"><i class="bi bi-list-ul me-2"></i>Ihre Weiterempfehlungen:</h6>
            <div class="table-responsive">
                <table id="referrals-table" class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Firma</th>
                            <th>E-Mail</th>
                            <th>Status</th>
                            <th>Datum</th>
                            <th class="text-end">Gutschrift</th>
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
                                        'pending' => 'Ausstehend',
                                        'credited' => 'Gutgeschrieben',
                                        'rejected' => 'Abgelehnt',
                                        default => $referral['status']
                                    };
                                    ?>
                                    <span class="badge <?= $statusBadge ?>">
                                        <?= $statusText ?>
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
