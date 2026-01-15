<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/form" class="text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Zurück zur Übersicht
        </a>
        <h1 class="h3 mb-0 mt-2">Formular bearbeiten</h1>
    </div>
    <span class="badge fs-6" style="background-color: <?= esc($category['color'] ?? '#6c757d') ?>">
        <?= esc($category['name']) ?>
    </span>
</div>

<form method="post" action="/admin/form/edit/<?= esc($form_id) ?>">
    <?= csrf_field() ?>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Formular-ID:</strong> <code><?= esc($form_id) ?></code>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3"><img src="https://flagcdn.com/w20/de.png" alt="DE" class="me-2">Deutsch</h5>

                    <div class="mb-3">
                        <label for="name_de" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name_de" name="name_de"
                               value="<?= esc($form['name_de'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="form_link_de" class="form-label">Formular-Link</label>
                        <input type="url" class="form-control" id="form_link_de" name="form_link_de"
                               value="<?= esc($form['form_link_de'] ?? '') ?>"
                               placeholder="https://example.com/formular/">
                    </div>
                </div>

                <div class="col-md-6">
                    <h5 class="mb-3"><img src="https://flagcdn.com/w20/gb.png" alt="EN" class="me-2">English</h5>

                    <div class="mb-3">
                        <label for="name_en" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name_en" name="name_en"
                               value="<?= esc($form['name_en'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="form_link_en" class="form-label">Form Link</label>
                        <input type="url" class="form-control" id="form_link_en" name="form_link_en"
                               value="<?= esc($form['form_link_en'] ?? '') ?>"
                               placeholder="https://example.com/en/form/">
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3"><img src="https://flagcdn.com/w20/fr.png" alt="FR" class="me-2">Français</h5>

                    <div class="mb-3">
                        <label for="name_fr" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="name_fr" name="name_fr"
                               value="<?= esc($form['name_fr'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="form_link_fr" class="form-label">Lien du formulaire</label>
                        <input type="url" class="form-control" id="form_link_fr" name="form_link_fr"
                               value="<?= esc($form['form_link_fr'] ?? '') ?>"
                               placeholder="https://example.com/fr/formulaire/">
                    </div>
                </div>

                <div class="col-md-6">
                    <h5 class="mb-3"><img src="https://flagcdn.com/w20/it.png" alt="IT" class="me-2">Italiano</h5>

                    <div class="mb-3">
                        <label for="name_it" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name_it" name="name_it"
                               value="<?= esc($form['name_it'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="form_link_it" class="form-label">Link del modulo</label>
                        <input type="url" class="form-control" id="form_link_it" name="form_link_it"
                               value="<?= esc($form['form_link_it'] ?? '') ?>"
                               placeholder="https://example.com/it/modulo/">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="/admin/form" class="btn btn-outline-secondary">Abbrechen</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Speichern
        </button>
    </div>
</form>
