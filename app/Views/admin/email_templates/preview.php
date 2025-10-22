<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Template Vorschau</h1>
        <div>
            <a href="/admin/email-templates/edit/<?= $template['id'] ?>" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Bearbeiten
            </a>
            <a href="/admin/email-templates" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Zurück
            </a>
        </div>
    </div>

    <!-- Offer Selection -->
    <?php
    // Load category types for German translations
    $categoryConfig = config('CategoryOptions');
    $categoryTypes = $categoryConfig->categoryTypes;
    $categoryTypes['default'] = 'Standard (Fallback)';
    ?>
    <?php if (!empty($offers)): ?>
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="bi bi-list-check"></i> Echte Offerte für Vorschau wählen
            </h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <label for="offerSelect" class="form-label">
                        <strong>Offerte auswählen:</strong>
                        <small class="text-muted">(<?= count($offers) ?> Offerten verfügbar<?= $template['offer_type'] !== 'default' ? ' für ' . esc($categoryTypes[$template['offer_type']] ?? $template['offer_type']) : '' ?>)</small>
                    </label>
                    <select id="offerSelect" class="form-select">
                        <?php foreach ($offers as $offer): ?>
                            <?php
                            $fields = json_decode($offer['form_fields'] ?? '{}', true);
                            $displayName = ($fields['vorname'] ?? '') . ' ' . ($fields['nachname'] ?? '');
                            $displayName = trim($displayName) ?: 'Unbekannt';
                            $city = $fields['city'] ?? $fields['ort'] ?? '';
                            $offerTypeLabel = $categoryTypes[$offer['type']] ?? $offer['type'];
                            ?>
                            <option value="<?= $offer['id'] ?>" <?= $selectedOfferId == $offer['id'] ? 'selected' : '' ?>>
                                #<?= $offer['id'] ?> - <?= esc($displayName) ?>
                                <?= $city ? '(' . esc($city) . ')' : '' ?>
                                - <?= date('d.m.Y', strtotime($offer['created_at'])) ?>
                                <?= $offer['type'] ? ' - ' . esc($offerTypeLabel) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button id="loadOffer" class="btn btn-primary w-100 mt-4">
                        <i class="bi bi-arrow-clockwise"></i> Vorschau laden
                    </button>
                </div>
            </div>

            <?php if ($selectedOffer): ?>
            <div class="alert alert-info mt-3 mb-0">
                <strong>Gewählte Offerte #<?= $selectedOffer['id'] ?>:</strong>
                <ul class="mb-0 mt-2 small">
                    <li><strong>Typ:</strong> <?= esc($categoryTypes[$selectedOffer['type']] ?? $selectedOffer['type']) ?></li>
                    <li><strong>Erstellt:</strong> <?= date('d.m.Y H:i', strtotime($selectedOffer['created_at'])) ?></li>
                    <li><strong>Felder:</strong> <?= count(json_decode($selectedOffer['form_fields'] ?? '{}', true)) ?> Felder</li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Info:</strong> No offers found<?= $template['offer_type'] !== 'default' ? ' for type "' . esc($template['offer_type']) . '"' : '' ?>. Using test data.
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Template Informationen</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Branche:</th>
                                    <td><?= esc($categoryTypes[$template['offer_type']] ?? $template['offer_type']) ?></td>
                                </tr>
                                <tr>
                                    <th>Sprache:</th>
                                    <td><?= strtoupper(esc($template['language'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <?php if ($template['is_active']): ?>
                                            <span class="badge bg-success">Aktiv</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Inaktiv</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Aktualisiert:</th>
                                    <td><?= date('d.m.Y H:i', strtotime($template['updated_at'])) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Preview -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-envelope"></i> E-Mail Vorschau
            </h5>
        </div>
        <div class="card-body">
            <div class="email-preview-container">
                <!-- Subject -->
                <div class="mb-3 p-3 bg-light rounded">
                    <strong>Betreff:</strong>
                    <div class="mt-2"><?= esc($subject) ?></div>
                </div>

                <!-- Body -->
                <div class="email-body p-4 border rounded" style="background-color: #fff;">
                    <?= $body ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Raw Template -->
    <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
            <button class="btn btn-sm btn-light float-end" type="button" data-bs-toggle="collapse" data-bs-target="#rawTemplate">
                <i class="bi bi-code"></i> Template Code anzeigen
            </button>
            <h5 class="mb-0">Template Quellcode</h5>
        </div>
        <div id="rawTemplate" class="collapse">
            <div class="card-body">
                <h6>Betreff Template:</h6>
                <pre class="bg-light p-3 rounded"><code><?= esc($template['subject']) ?></code></pre>

                <h6 class="mt-3">Body Template:</h6>
                <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code><?= esc($template['body_template']) ?></code></pre>
            </div>
        </div>
    </div>
</div>

<style>
.email-preview-container {
    max-width: 800px;
    margin: 0 auto;
}

.email-body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
}

.email-body .highlight {
    background-color: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 15px;
    margin: 20px 0;
}

.email-body h2 {
    color: #333;
    font-size: 24px;
    margin-bottom: 20px;
}

.email-body h3 {
    color: #555;
    font-size: 18px;
    margin-top: 25px;
    margin-bottom: 15px;
}

.email-body ul {
    padding-left: 20px;
}

.email-body li {
    margin-bottom: 8px;
}

pre code {
    font-size: 0.85rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const offerSelect = document.getElementById('offerSelect');
    const loadButton = document.getElementById('loadOffer');

    if (loadButton && offerSelect) {
        loadButton.addEventListener('click', function() {
            const offerId = offerSelect.value;
            const templateId = <?= $template['id'] ?>;

            // Reload page with selected offer
            window.location.href = `/admin/email-templates/preview/${templateId}?offer_id=${offerId}`;
        });

        // Also allow Enter key in dropdown
        offerSelect.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                loadButton.click();
            }
        });
    }
});
</script>

<?= $this->endSection() ?>
