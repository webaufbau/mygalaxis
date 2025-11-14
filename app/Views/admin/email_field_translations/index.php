<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-translate me-2"></i>E-Mail Feldwerte-Übersetzungen</h2>
        <a href="<?= site_url('admin/email-templates') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Zurück zu Templates
        </a>
    </div>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-globe me-2"></i>Globale Feldwerte-Übersetzungen</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Wichtig:</strong> Diese Übersetzungen gelten für <strong>ALLE E-Mail-Templates</strong>!
                        <br><strong>Was wird übersetzt:</strong> Deutsche Texte/Labels im Template (z.B. "Eigentümer", "Anzahl Zimmer")
                        <br><strong>Format:</strong> Eine Zeile pro Übersetzung: <code>Deutscher Text=Übersetzung</code>
                        <br><strong>Beispiel:</strong> <code>Eigentümer=Propriétaire</code>
                    </div>

                    <form method="POST" action="<?= site_url('admin/email-field-translations') ?>">
                        <?= csrf_field() ?>

                        <!-- Englisch -->
                        <div class="mb-4">
                            <label for="translation_en" class="form-label">
                                <i class="bi bi-flag me-1"></i> <strong>Englisch (EN)</strong>
                            </label>
                            <textarea class="form-control font-monospace"
                                      id="translation_en"
                                      name="translation_en"
                                      rows="15"
                                      placeholder="Eigentümer=Owner&#10;Anzahl Zimmer=Number of rooms&#10;Wohnfläche=Living area&#10;Umzugsdatum=Moving date&#10;Adresse=Address"><?= esc($translations['en']) ?></textarea>
                            <small class="form-text text-muted">
                                Format: <code>Deutsch=English</code> (eine Übersetzung pro Zeile)
                            </small>
                        </div>

                        <!-- Französisch -->
                        <div class="mb-4">
                            <label for="translation_fr" class="form-label">
                                <i class="bi bi-flag me-1"></i> <strong>Französisch (FR)</strong>
                            </label>
                            <textarea class="form-control font-monospace"
                                      id="translation_fr"
                                      name="translation_fr"
                                      rows="15"
                                      placeholder="Eigentümer=Propriétaire&#10;Anzahl Zimmer=Nombre de pièces&#10;Wohnfläche=Surface habitable&#10;Umzugsdatum=Date de déménagement&#10;Adresse=Adresse"><?= esc($translations['fr']) ?></textarea>
                            <small class="form-text text-muted">
                                Format: <code>Deutsch=Français</code> (une traduction par ligne)
                            </small>
                        </div>

                        <!-- Italienisch -->
                        <div class="mb-4">
                            <label for="translation_it" class="form-label">
                                <i class="bi bi-flag me-1"></i> <strong>Italienisch (IT)</strong>
                            </label>
                            <textarea class="form-control font-monospace"
                                      id="translation_it"
                                      name="translation_it"
                                      rows="15"
                                      placeholder="Eigentümer=Proprietario&#10;Anzahl Zimmer=Numero di stanze&#10;Wohnfläche=Superficie abitabile&#10;Umzugsdatum=Data del trasloco&#10;Adresse=Indirizzo"><?= esc($translations['it']) ?></textarea>
                            <small class="form-text text-muted">
                                Formato: <code>Tedesco=Italiano</code> (una traduzione per riga)
                            </small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= site_url('admin/email-templates') ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Abbrechen
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Übersetzungen speichern
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Hinweise</h5>
                </div>
                <div class="card-body">
                    <h6><i class="bi bi-check-circle text-success me-1"></i> Was wird übersetzt?</h6>
                    <ul>
                        <li>Deutsche Texte/Labels im Template</li>
                        <li>Werte in <code>{field:xyz}</code></li>
                        <li>Werte in <code>[field:xyz]</code></li>
                        <li>Statische Textpassagen</li>
                    </ul>

                    <hr>

                    <h6><i class="bi bi-x-circle text-danger me-1"></i> Was wird NICHT übersetzt?</h6>
                    <ul>
                        <li>HTML-Tags und Struktur</li>
                        <li>Bedingungen <code>[if ...]</code></li>
                        <li>Platzhalter-Namen selbst</li>
                    </ul>

                    <hr>

                    <h6><i class="bi bi-code-square me-1"></i> Beispiel</h6>
                    <div class="bg-light p-2 rounded mb-2">
                        <small><strong>Vorher (DE):</strong></small><br>
                        <code style="font-size: 11px;">Eigentümer</code>
                    </div>
                    <div class="bg-light p-2 rounded mb-2">
                        <small><strong>Nachher (FR):</strong></small><br>
                        <code style="font-size: 11px;">Propriétaire</code>
                    </div>

                    <hr>

                    <div class="alert alert-warning mb-0">
                        <small>
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Tipp:</strong> Diese Übersetzungen gelten für ALLE E-Mail-Templates gleichzeitig!
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
