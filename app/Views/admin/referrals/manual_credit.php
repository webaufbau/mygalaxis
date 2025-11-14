<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-cash-coin me-2"></i>Manuelle Gutschrift</h2>
        <div>
            <a href="<?= site_url('admin/referrals') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Zurück zur Übersicht
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Gutschrift für Firma erstellen</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Mit diesem Formular können Sie einer Firma manuell eine Gutschrift geben, unabhängig von Weiterempfehlungen.
                    </p>

                    <form method="post" action="<?= site_url('admin/referrals/manual-credit') ?>">
                        <?= csrf_field() ?>

                        <!-- Firma auswählen -->
                        <div class="mb-3">
                            <label for="company_id" class="form-label">Firma <span class="text-danger">*</span></label>
                            <select name="company_id" id="company_id" class="form-select" required>
                                <option value="">-- Firma auswählen --</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?= esc($company['id']) ?>"
                                            <?= old('company_id') == $company['id'] ? 'selected' : '' ?>>
                                        <?= esc($company['company_name']) ?: esc($company['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                Wählen Sie die Firma aus, die die Gutschrift erhalten soll.
                            </small>
                        </div>

                        <!-- Betrag -->
                        <div class="mb-3">
                            <label for="amount" class="form-label">Betrag (CHF) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                                   class="form-control" value="<?= old('amount') ?: '50.00' ?>" required>
                            <small class="form-text text-muted">
                                Geben Sie den Gutschrift-Betrag in CHF ein.
                            </small>
                        </div>

                        <!-- Grund -->
                        <div class="mb-3">
                            <label for="reason" class="form-label">Grund <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reason" class="form-control" rows="4" required><?= old('reason') ?></textarea>
                            <small class="form-text text-muted">
                                Beschreiben Sie den Grund für diese Gutschrift (wird in der Buchungshistorie angezeigt).
                            </small>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= site_url('admin/referrals') ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Abbrechen
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i>Gutschrift geben
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert alert-info mt-4">
                <h6><i class="bi bi-info-circle me-2"></i>Hinweise:</h6>
                <ul class="mb-0">
                    <li>Die Gutschrift wird sofort dem Guthaben-Konto der Firma gutgeschrieben.</li>
                    <li>Eine Buchung vom Typ "topup" wird automatisch erstellt.</li>
                    <li>Der angegebene Grund wird in der Finanzverwaltung der Firma angezeigt.</li>
                    <li>Diese Aktion wird im System-Log festgehalten.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
