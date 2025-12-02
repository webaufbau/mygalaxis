<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<style>
.form-section {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}
.form-section h5 {
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
    font-size: 1rem;
}
.field-label {
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
}
.edit-info {
    background: #e7f3ff;
    border-left: 4px solid #0d6efd;
    padding: 0.75rem 1rem;
    border-radius: 0 4px 4px 0;
    font-size: 0.875rem;
}
.company-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}
.company-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.company-item:last-child {
    border-bottom: none;
}
.company-item:hover {
    background: #f8f9fa;
}
.company-item.selected {
    background: #d1e7dd;
}
.filter-section {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
</style>

<?php
$isAlreadySent = !empty($offer['companies_notified_at']);
$typeName = $typeMapping[$offer['type']] ?? ucfirst($offer['type']);
$formFields = json_decode($offer['form_fields'] ?? '{}', true);
$selectedCompanies = json_decode($offer['selected_companies'] ?? '[]', true);
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/offers/pending" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left"></i> Zurück zur Liste
        </a>
        <h2 class="mb-0">
            <span class="badge bg-primary"><?= esc($typeName) ?></span>
            Anfrage #<?= $offer['id'] ?>
        </h2>
        <small class="text-muted">
            <?= esc($offer['zip']) ?> <?= esc($offer['city']) ?> &bull;
            Erstellt: <?= \CodeIgniter\I18n\Time::parse($offer['created_at'])->setTimezone(app_timezone())->format('d.m.Y H:i') ?>
        </small>
    </div>
    <div>
        <?php if ($isAlreadySent): ?>
            <span class="badge bg-success fs-6">
                <i class="bi bi-check-circle"></i> Versendet am <?= date('d.m.Y H:i', strtotime($offer['companies_notified_at'])) ?>
            </span>
        <?php endif; ?>
    </div>
</div>

<!-- Bearbeitungs-Info -->
<?php if (!empty($offer['edited_at'])): ?>
    <div class="edit-info mb-4">
        <i class="bi bi-pencil-fill me-2"></i>
        <strong>Zuletzt bearbeitet</strong> von <?= esc($editedByUser ?? 'Admin') ?>
        am <?= date('d.m.Y \u\m H:i', strtotime($offer['edited_at'])) ?> Uhr
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form action="/admin/offers/update/<?= $offer['id'] ?>" method="post" id="offerForm">
    <?= csrf_field() ?>
    <input type="hidden" name="selected_companies" id="selectedCompaniesInput" value="<?= esc(json_encode($selectedCompanies)) ?>">

    <div class="row">
        <!-- Linke Spalte: Offerten-Details -->
        <div class="col-lg-6">
            <!-- Kunde -->
            <div class="form-section">
                <h5><i class="bi bi-person"></i> Kundendaten</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="field-label">Name</label>
                        <input type="text" class="form-control" value="<?= esc(($formFields['vorname'] ?? '') . ' ' . ($formFields['nachname'] ?? '')) ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="field-label">Telefon</label>
                        <input type="text" class="form-control" value="<?= esc($formFields['phone'] ?? '') ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="field-label">E-Mail</label>
                        <input type="email" class="form-control" value="<?= esc($formFields['email'] ?? '') ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="field-label">Adresse</label>
                        <?php
                        $address = $formFields['address'] ?? $formFields['auszug_adresse'] ?? [];
                        if (is_array($address)) {
                            $street = $address['address_line_1'] ?? $address['street'] ?? '';
                            $number = $address['address_line_2'] ?? '';
                            $addressStr = trim($street . ' ' . $number) . ', ' . ($address['zip'] ?? '') . ' ' . ($address['city'] ?? '');
                        } else {
                            $addressStr = '';
                        }
                        ?>
                        <input type="text" class="form-control" value="<?= esc($addressStr) ?>" readonly>
                    </div>
                </div>
            </div>

            <!-- Branche/Typ -->
            <div class="form-section">
                <h5><i class="bi bi-tag"></i> Branche / Typ</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="field-label">Hauptkategorie</label>
                        <select name="type" class="form-select" <?= $isAlreadySent ? 'disabled' : '' ?>>
                            <?php foreach ($typeMapping as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $offer['type'] === $key ? 'selected' : '' ?>>
                                    <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="field-label">Untertyp</label>
                        <select name="original_type" class="form-select" <?= $isAlreadySent ? 'disabled' : '' ?>>
                            <optgroup label="Umzug">
                                <option value="umzug_privat" <?= ($offer['original_type'] ?? '') === 'umzug_privat' ? 'selected' : '' ?>>Privat-Umzug</option>
                                <option value="umzug_firma" <?= ($offer['original_type'] ?? '') === 'umzug_firma' ? 'selected' : '' ?>>Firmen-Umzug</option>
                            </optgroup>
                            <optgroup label="Reinigung">
                                <option value="reinigung_wohnung" <?= ($offer['original_type'] ?? '') === 'reinigung_wohnung' ? 'selected' : '' ?>>Wohnungsreinigung</option>
                                <option value="reinigung_haus" <?= ($offer['original_type'] ?? '') === 'reinigung_haus' ? 'selected' : '' ?>>Hausreinigung</option>
                                <option value="reinigung_gewerbe" <?= ($offer['original_type'] ?? '') === 'reinigung_gewerbe' ? 'selected' : '' ?>>Gewerbereinigung</option>
                                <option value="reinigung_nur_fenster" <?= ($offer['original_type'] ?? '') === 'reinigung_nur_fenster' ? 'selected' : '' ?>>Fensterreinigung</option>
                                <option value="reinigung_fassaden" <?= ($offer['original_type'] ?? '') === 'reinigung_fassaden' ? 'selected' : '' ?>>Fassadenreinigung</option>
                                <option value="reinigung_hauswartung" <?= ($offer['original_type'] ?? '') === 'reinigung_hauswartung' ? 'selected' : '' ?>>Hauswartung</option>
                                <option value="reinigung_andere" <?= ($offer['original_type'] ?? '') === 'reinigung_andere' ? 'selected' : '' ?>>Andere Reinigung</option>
                            </optgroup>
                            <optgroup label="Maler/Gipser">
                                <option value="maler_wohnung" <?= ($offer['original_type'] ?? '') === 'maler_wohnung' ? 'selected' : '' ?>>Wohnung</option>
                                <option value="maler_haus" <?= ($offer['original_type'] ?? '') === 'maler_haus' ? 'selected' : '' ?>>Haus</option>
                                <option value="maler_gewerbe" <?= ($offer['original_type'] ?? '') === 'maler_gewerbe' ? 'selected' : '' ?>>Gewerbe</option>
                                <option value="maler_andere" <?= ($offer['original_type'] ?? '') === 'maler_andere' ? 'selected' : '' ?>>Andere</option>
                            </optgroup>
                            <optgroup label="Garten">
                                <option value="garten_allgemeine_gartenpflege" <?= ($offer['original_type'] ?? '') === 'garten_allgemeine_gartenpflege' ? 'selected' : '' ?>>Gartenpflege</option>
                                <option value="garten_garten_umgestalten" <?= ($offer['original_type'] ?? '') === 'garten_garten_umgestalten' ? 'selected' : '' ?>>Garten umgestalten</option>
                                <option value="garten_neue_gartenanlage" <?= ($offer['original_type'] ?? '') === 'garten_neue_gartenanlage' ? 'selected' : '' ?>>Neue Gartenanlage</option>
                                <option value="garten_andere_gartenarbeiten" <?= ($offer['original_type'] ?? '') === 'garten_andere_gartenarbeiten' ? 'selected' : '' ?>>Andere Gartenarbeiten</option>
                            </optgroup>
                            <optgroup label="Handwerker">
                                <option value="elektriker" <?= ($offer['original_type'] ?? '') === 'elektriker' ? 'selected' : '' ?>>Elektriker</option>
                                <option value="sanitaer" <?= ($offer['original_type'] ?? '') === 'sanitaer' ? 'selected' : '' ?>>Sanitär</option>
                                <option value="heizung" <?= ($offer['original_type'] ?? '') === 'heizung' ? 'selected' : '' ?>>Heizung</option>
                                <option value="plattenleger" <?= ($offer['original_type'] ?? '') === 'plattenleger' ? 'selected' : '' ?>>Plattenleger</option>
                                <option value="bodenleger" <?= ($offer['original_type'] ?? '') === 'bodenleger' ? 'selected' : '' ?>>Bodenleger</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Preis & Optionen -->
            <div class="form-section">
                <h5><i class="bi bi-currency-dollar"></i> Preis & Optionen</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="field-label">Berechneter Preis</label>
                        <div class="form-control-plaintext">
                            <strong>CHF <?= number_format($offer['price'], 0, '.', "'") ?></strong>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="field-label">Angepasster Preis (CHF)</label>
                        <input type="number" name="custom_price" class="form-control"
                               value="<?= esc($offer['custom_price'] ?? '') ?>"
                               placeholder="<?= number_format($offer['price'], 0) ?>"
                               <?= $isAlreadySent ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="field-label">&nbsp;</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_test" id="isTest"
                                   <?= $offer['is_test'] ? 'checked' : '' ?> <?= $isAlreadySent ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="isTest">
                                <i class="bi bi-flask text-warning"></i> Testanfrage
                                <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" data-bs-placement="top"
                                   title="Testanfragen werden NUR an Test-Firmen gesendet, nicht an echte Kunden."></i>
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            <?php if ($offer['is_test']): ?>
                                <i class="bi bi-arrow-right"></i> Wird nur an <strong>Test-Firmen</strong> gesendet
                            <?php else: ?>
                                <i class="bi bi-arrow-right"></i> Wird an <strong>echte Firmen</strong> gesendet
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Notizen -->
            <div class="form-section">
                <h5><i class="bi bi-sticky"></i> Notizen</h5>
                <div class="mb-3">
                    <label class="field-label">Interne Notiz (nur für Admin)</label>
                    <textarea name="admin_notes" class="form-control" rows="2"
                              placeholder="Interne Bemerkungen..."><?= esc($offer['admin_notes'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="field-label">Hinweis für Firmen</label>
                    <textarea name="customer_hint" class="form-control" rows="2"
                              placeholder="Wird den Firmen in der Offerte angezeigt..."><?= esc($offer['customer_hint'] ?? '') ?></textarea>
                    <small class="text-muted">Dieser Hinweis erscheint in der Anfrage, die an Firmen gesendet wird</small>
                </div>
            </div>

            <!-- Änderungsprotokoll -->
            <?php if (!empty($auditLogs)): ?>
            <div class="form-section">
                <h5><i class="bi bi-clock-history"></i> Änderungsprotokoll</h5>
                <div class="audit-log-list" style="max-height: 300px; overflow-y: auto;">
                    <?php
                    $actionLabels = [
                        'offer_updated' => 'Bearbeitet',
                        'offer_approved' => 'Freigegeben',
                        'offer_created' => 'Erstellt',
                    ];
                    $fieldLabels = [
                        'type' => 'Kategorie',
                        'original_type' => 'Untertyp',
                        'custom_price' => 'Angepasster Preis',
                        'discounted_price' => 'Rabattpreis',
                        'is_test' => 'Testanfrage',
                        'admin_notes' => 'Interne Notiz',
                        'customer_hint' => 'Hinweis für Firmen',
                        'zip' => 'PLZ',
                        'city' => 'Ort',
                        'sent_to_companies' => 'An Firmen gesendet',
                    ];
                    ?>
                    <?php foreach ($auditLogs as $log): ?>
                        <div class="audit-entry border-bottom py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?= esc($actionLabels[$log['action']] ?? $log['action']) ?></strong>
                                    <span class="text-muted">von <?= esc($log['user_name']) ?></span>
                                </div>
                                <small class="text-muted"><?= date('d.m.Y H:i', strtotime($log['created_at'])) ?></small>
                            </div>
                            <?php
                            $oldValues = json_decode($log['old_values'] ?? '{}', true) ?: [];
                            $newValues = json_decode($log['new_values'] ?? '{}', true) ?: [];
                            if (!empty($newValues)):
                            ?>
                                <div class="mt-1 small">
                                    <?php foreach ($newValues as $field => $newVal): ?>
                                        <?php
                                        $oldVal = $oldValues[$field] ?? '-';
                                        $fieldLabel = $fieldLabels[$field] ?? $field;
                                        // Formatiere Werte
                                        if ($field === 'is_test') {
                                            $oldVal = $oldVal ? 'Ja' : 'Nein';
                                            $newVal = $newVal ? 'Ja' : 'Nein';
                                        }
                                        ?>
                                        <div class="text-muted">
                                            <i class="bi bi-arrow-right"></i>
                                            <strong><?= esc($fieldLabel) ?>:</strong>
                                            <?php if ($log['action'] === 'offer_approved'): ?>
                                                <?= esc($newVal) ?>
                                            <?php else: ?>
                                                <span class="text-danger"><?= esc($oldVal) ?></span>
                                                → <span class="text-success"><?= esc($newVal) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Rechte Spalte: Firmen-Auswahl -->
        <div class="col-lg-6">
            <div class="form-section">
                <h5><i class="bi bi-buildings"></i> Firmen für diese Offerte</h5>

                <!-- Filter -->
                <div class="filter-section">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="field-label small">PLZ / Umkreis</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="filterZip" value="<?= esc($offer['zip']) ?>" placeholder="PLZ">
                                <select class="form-select" id="filterRadius" style="max-width: 100px;">
                                    <option value="10">10 km</option>
                                    <option value="20" selected>20 km</option>
                                    <option value="50">50 km</option>
                                    <option value="100">100 km</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="field-label small">Branche</label>
                            <select class="form-select form-select-sm" id="filterCategory">
                                <option value="">Alle Branchen</option>
                                <?php foreach ($typeMapping as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $offer['type'] === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="field-label small">Status</label>
                            <select class="form-select form-select-sm" id="filterBlocked">
                                <option value="">Alle</option>
                                <option value="active" selected>Nur aktive</option>
                                <option value="blocked">Nur blockierte</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="field-label small">&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-sm w-100" id="searchCompanies">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Firmen-Liste -->
                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <span class="text-muted small" id="companyCount">Klicke "Suchen" um Firmen zu laden</span>
                    <div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAll">Alle</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="selectNone">Keine</button>
                    </div>
                </div>

                <div class="company-list" id="companyList">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-building fs-1 opacity-50"></i>
                        <p class="mt-2 mb-0">Nutze die Filter oben um passende Firmen zu finden</p>
                    </div>
                </div>

                <!-- Zusammenfassung -->
                <div class="mt-3 p-3 bg-light rounded">
                    <div class="d-flex justify-content-between">
                        <span><strong id="selectedCount">0</strong> Firmen ausgewählt</span>
                        <?php if ($offer['is_test']): ?>
                            <span class="badge bg-warning text-dark">Nur Test-Firmen</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Aktions-Buttons -->
            <div class="form-section">
                <div class="d-flex gap-2 justify-content-end">
                    <a href="/admin/offers/pending" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i> Abbrechen
                    </a>
                    <button type="submit" name="action" value="save" class="btn btn-primary">
                        <i class="bi bi-save"></i> Speichern
                    </button>
                    <?php if (!$isAlreadySent): ?>
                        <button type="submit" name="action" value="save_and_send" class="btn btn-success"
                                onclick="return confirm('Offerte wirklich an die ausgewählten Firmen senden?');">
                            <i class="bi bi-send"></i> Speichern & Senden
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tooltips initialisieren
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    const companyList = document.getElementById('companyList');
    const companyCount = document.getElementById('companyCount');
    const selectedCount = document.getElementById('selectedCount');
    const selectedCompaniesInput = document.getElementById('selectedCompaniesInput');

    let selectedCompanies = JSON.parse(selectedCompaniesInput.value || '[]');
    let allCompanies = [];

    function updateSelectedCount() {
        selectedCount.textContent = selectedCompanies.length;
        selectedCompaniesInput.value = JSON.stringify(selectedCompanies);
    }

    function renderCompanies(companies) {
        allCompanies = companies;
        if (companies.length === 0) {
            companyList.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="bi bi-exclamation-circle fs-1 opacity-50"></i>
                    <p class="mt-2 mb-0">Keine Firmen gefunden</p>
                </div>
            `;
            companyCount.textContent = 'Keine Firmen gefunden';
            return;
        }

        companyCount.textContent = `${companies.length} Firmen gefunden`;

        let html = '';
        companies.forEach(c => {
            const isSelected = selectedCompanies.includes(c.id);
            const isTest = c.is_test == 1 || c.is_test === true;
            const isBlocked = c.is_blocked == 1 || c.is_blocked === true;
            const badges = [];
            if (isTest) badges.push('<span class="badge bg-warning text-dark">Test</span>');
            if (isBlocked) badges.push('<span class="badge bg-danger">Blockiert</span>');

            html += `
                <div class="company-item ${isSelected ? 'selected' : ''} ${isBlocked ? 'blocked' : ''}" data-id="${c.id}">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input company-checkbox" role="switch"
                               data-id="${c.id}" ${isSelected ? 'checked' : ''} ${isBlocked ? 'disabled' : ''}>
                    </div>
                    <div class="flex-grow-1">
                        <strong>${c.company_name || c.contact_person || 'Unbenannt'}</strong>
                        ${badges.join(' ')}
                        <br>
                        <small class="text-muted">${c.zip} ${c.city} • ${c.distance_km} km</small>
                    </div>
                    <a href="/admin/user/${c.id}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Firma öffnen">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
            `;
        });
        companyList.innerHTML = html;

        // Checkbox Events
        document.querySelectorAll('.company-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const id = parseInt(this.dataset.id);
                const item = this.closest('.company-item');

                if (this.checked) {
                    if (!selectedCompanies.includes(id)) {
                        selectedCompanies.push(id);
                    }
                    item.classList.add('selected');
                } else {
                    selectedCompanies = selectedCompanies.filter(x => x !== id);
                    item.classList.remove('selected');
                }
                updateSelectedCount();
            });
        });
    }

    // Suche
    document.getElementById('searchCompanies').addEventListener('click', function() {
        const zip = document.getElementById('filterZip').value;
        const radius = document.getElementById('filterRadius').value;
        const category = document.getElementById('filterCategory').value;
        const blocked = document.getElementById('filterBlocked').value;

        companyList.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

        fetch(`/admin/offers/search-companies?zipcode=${zip}&radius=${radius}&category=${category}&blocked=${blocked}`)
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    companyList.innerHTML = `<div class="alert alert-danger m-3">${data.error}</div>`;
                    return;
                }
                renderCompanies(data.companies || []);
            })
            .catch(err => {
                companyList.innerHTML = '<div class="alert alert-danger m-3">Fehler bei der Suche</div>';
            });
    });

    // Alle/Keine auswählen
    document.getElementById('selectAll').addEventListener('click', function() {
        allCompanies.forEach(c => {
            if (!c.is_blocked && !selectedCompanies.includes(c.id)) {
                selectedCompanies.push(c.id);
            }
        });
        document.querySelectorAll('.company-checkbox:not(:disabled)').forEach(cb => {
            cb.checked = true;
            cb.closest('.company-item').classList.add('selected');
        });
        updateSelectedCount();
    });

    document.getElementById('selectNone').addEventListener('click', function() {
        selectedCompanies = [];
        document.querySelectorAll('.company-checkbox').forEach(cb => {
            cb.checked = false;
            cb.closest('.company-item').classList.remove('selected');
        });
        updateSelectedCount();
    });

    updateSelectedCount();
});
</script>

<?= $this->endSection() ?>
