<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= esc($title) ?></h2>
    <a href="/admin/projects" class="btn btn-outline-secondary">Zurück zur Liste</a>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<form method="post" action="<?= $project ? '/admin/projects/update/' . $project['id'] : '/admin/projects/store' ?>">
    <?= csrf_field() ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Grunddaten</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug (eindeutig) *</label>
                        <input type="text"
                               name="slug"
                               id="slug"
                               class="form-control"
                               value="<?= esc(old('slug', $project['slug'] ?? '')) ?>"
                               required
                               pattern="[a-z0-9_-]+"
                               placeholder="z.B. bathroom_renovation">
                        <small class="text-muted">Nur Kleinbuchstaben, Zahlen, Unterstriche und Bindestriche.</small>
                    </div>

                    <div class="mb-3">
                        <label for="category_type" class="form-label">Ziel-Branche *</label>
                        <select name="category_type" id="category_type" class="form-select" required>
                            <option value="">-- Ziel-Branche wählen --</option>
                            <?php
                            $categoryOptions = config('CategoryOptions');
                            $currentCategory = old('category_type', $project['category_type'] ?? '');
                            foreach ($categoryOptions->categoryTypes as $key => $name):
                            ?>
                                <option value="<?= esc($key) ?>" <?= $currentCategory === $key ? 'selected' : '' ?>>
                                    <?= esc($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Das Projekt verwendet den Formular-Link dieser Branche.</small>
                    </div>

                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sortierung</label>
                        <input type="number"
                               name="sort_order"
                               id="sort_order"
                               class="form-control"
                               value="<?= esc(old('sort_order', $project['sort_order'] ?? 0)) ?>"
                               min="0"
                               style="width: 100px;">
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox"
                                   name="is_active"
                                   id="is_active"
                                   class="form-check-input"
                                   value="1"
                                   <?= old('is_active', $project['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label for="is_active" class="form-check-label">Aktiv</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Namen (mehrsprachig)</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name_de" class="form-label">Name (Deutsch) *</label>
                        <input type="text"
                               name="name_de"
                               id="name_de"
                               class="form-control"
                               value="<?= esc(old('name_de', $project['name_de'] ?? '')) ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="name_en" class="form-label">Name (English)</label>
                        <input type="text"
                               name="name_en"
                               id="name_en"
                               class="form-control"
                               value="<?= esc(old('name_en', $project['name_en'] ?? '')) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="name_fr" class="form-label">Name (Français)</label>
                        <input type="text"
                               name="name_fr"
                               id="name_fr"
                               class="form-control"
                               value="<?= esc(old('name_fr', $project['name_fr'] ?? '')) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="name_it" class="form-label">Name (Italiano)</label>
                        <input type="text"
                               name="name_it"
                               id="name_it"
                               class="form-control"
                               value="<?= esc(old('name_it', $project['name_it'] ?? '')) ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <?= $project ? 'Speichern' : 'Erstellen' ?>
        </button>
        <a href="/admin/projects" class="btn btn-outline-secondary">Abbrechen</a>
    </div>
</form>

<?= $this->endSection() ?>
