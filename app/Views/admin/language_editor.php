<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
function renderInputs($translations, $namePrefix) {
    $output = '';
    foreach ($translations as $key => $value) {
        if (is_array($value)) {
            $output .= renderInputs($value, $namePrefix . '[' . $key . ']');
        } else {
            $output .= '<div class="form-group mb-2">
                        <label for="' . $namePrefix . '[' . $key . ']">' . $key . '</label>
                        <input type="text" class="form-control" id="' . $namePrefix . '[' . $key . ']" name="' . $namePrefix . '[' . $key . ']" value="' . htmlspecialchars($value, ENT_QUOTES) . '">
                    </div>';
        }
    }
    return $output;
}

?>

<div class="container-fluid py-4">
    <h2>Texte</h2>

    <form action="<?= site_url('admin/language-editor/update'); ?>" method="post" id="language-form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="file-select">Wählen Sie ein Thema aus:</label>
            <select class="form-control form-select mb-3" id="file-select" onchange="showForm()">
                <option value="">Bitte wählen...</option>
                <?php foreach ($languages as $language => $files): ?>
                    <optgroup label="<?= ucfirst($language) ?>">
                        <?php foreach ($files as $filename => $translations): ?>
                            <option value="<?= $language . '/' . $filename ?>"><?= $filename ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="file-form">
            <?php foreach ($languages as $language => $files): ?>
                <?php foreach ($files as $filename => $translations): ?>
                    <div class="file-form-content" id="<?= $language . '-' . $filename ?>" style="display:none;">
                        <h3 class="mt-3"><?= $filename ?></h3>
                        <p class="text-muted d-inline-block m-0">HTML-Tags wie Links, Umbrüche, ... sind erlaubt.</p>
                        <?= renderInputs($translations, $language . '[' . $filename . ']'); ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary" id="save-button" style="display: none;">Speichern</button>
    </form>

    <h2 class="mt-5">Suche</h2>
    <form action="<?= site_url('admin/language-editor/search'); ?>" method="get">
        <div class="form-group mb-3">
            <label for="search-term">Suchbegriff:</label>
            <input type="text" class="form-control" id="search-term" name="search-term" placeholder="Suchbegriff eingeben" value="<?=$search_term;?>">
        </div>
        <button type="submit" class="btn btn-secondary">Suchen</button>
    </form>

    <?php if (isset($searchResults)): ?>
        <h3 class="mt-4">Suchergebnisse:</h3>
        <ul>
            <?php foreach ($searchResults as $file): ?>
                <li><?= htmlspecialchars(basename($file), ENT_QUOTES); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<script>
    function showForm() {
        var selectedFile = document.getElementById("file-select").value;
        var fileForms = document.querySelectorAll(".file-form-content");
        var saveButton = document.getElementById("save-button");

        // Alle Formularabschnitte ausblenden
        fileForms.forEach(function(form) {
            form.style.display = "none";
        });

        // Speichern-Button ausblenden
        saveButton.style.display = "none";

        // Wenn eine Auswahl getroffen wurde, das entsprechende Formular anzeigen
        if (selectedFile) {
            var formId = selectedFile.replace('/', '-');
            var selectedForm = document.getElementById(formId);
            if (selectedForm) {
                selectedForm.style.display = "block";
                saveButton.style.display = "inline-block"; // Speichern-Button anzeigen
            }
        }
    }
</script>

<?= $this->endSection() ?>
