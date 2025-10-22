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

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Info:</strong> Dies ist eine Vorschau mit Testdaten. Die tatsächliche E-Mail wird mit echten Formulardaten gefüllt.
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Template Info</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Offer Type:</th>
                            <td><?= esc($template['offer_type']) ?></td>
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

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Testdaten</h5>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        Diese Daten werden für die Vorschau verwendet:
                    </small>
                    <ul class="small mt-2">
                        <li>vorname: Max</li>
                        <li>nachname: Mustermann</li>
                        <li>email: max@example.com</li>
                        <li>phone: +41 79 123 45 67</li>
                        <li>umzugsdatum: 15/12/2025</li>
                        <li>anzahl_zimmer: 4</li>
                        <li>qm: 85</li>
                    </ul>
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

<?= $this->endSection() ?>
