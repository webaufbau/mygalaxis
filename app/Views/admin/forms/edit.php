<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/form" class="text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Zurück zur Übersicht
        </a>
        <h1 class="h3 mb-0 mt-2">Formular bearbeiten</h1>
    </div>
</div>

<form method="post" action="/admin/form/edit/<?= esc($form_id) ?>">
    <?= csrf_field() ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>Aktuelle ID:</strong> <code id="currentFormId"><?= esc($form_id) ?></code>
            </div>
            <span class="badge fs-6" id="categoryBadge" style="background-color: <?= esc($category['color'] ?? '#6c757d') ?>">
                <?= esc($category['name']) ?>
            </span>
        </div>
        <div class="card-body">
            <!-- Branche ändern -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="category_key" class="form-label">Branche</label>
                    <select class="form-select" id="category_key" name="category_key">
                        <?php foreach ($categories as $key => $cat): ?>
                        <option value="<?= esc($key) ?>"
                                data-color="<?= esc($cat['color'] ?? '#6c757d') ?>"
                                data-name="<?= esc($cat['name']) ?>"
                                <?= $key === $category_key ? 'selected' : '' ?><?= !empty($cat['hidden']) ? ' class="text-muted"' : '' ?>>
                            <?= esc($cat['name']) ?><?= !empty($cat['hidden']) ? ' (nur für Projekte)' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <div id="categoryChangeWarning" class="alert alert-warning mb-0 mt-4" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Achtung:</strong> Die Formular-ID ändert sich zu <code id="newFormId"></code>
                        <br><small class="text-muted">Bestehende Verknüpfungen (z.B. Projekte) werden ungültig!</small>
                    </div>
                </div>
            </div>

            <hr class="my-4">

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_key');
    const warning = document.getElementById('categoryChangeWarning');
    const newFormIdSpan = document.getElementById('newFormId');
    const categoryBadge = document.getElementById('categoryBadge');
    const originalCategory = '<?= esc($category_key) ?>';

    // Zähle Formulare pro Kategorie für neue ID-Berechnung
    const formCounts = <?= json_encode($form_counts ?? []) ?>;

    categorySelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const newCategory = this.value;
        const color = selectedOption.dataset.color;
        const name = selectedOption.dataset.name;

        // Badge aktualisieren
        categoryBadge.style.backgroundColor = color;
        categoryBadge.textContent = name;

        if (newCategory !== originalCategory) {
            // Neue ID berechnen (Index = Anzahl existierender Formulare in neuer Kategorie)
            const newIndex = formCounts[newCategory] || 0;
            newFormIdSpan.textContent = newCategory + ':' + newIndex;
            warning.style.display = 'block';
        } else {
            warning.style.display = 'none';
        }
    });
});
</script>
