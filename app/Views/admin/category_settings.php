<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!--<h2>Kategorien bearbeiten</h2>-->

<?php
function makeOptionKey(string $label, array $existingKeys): string
{
    // Kleinbuchstaben, Leerzeichen durch Unterstrich ersetzen, Sonderzeichen entfernen
    $key = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $label));

    // Falls schon vorhanden, mit Zahl ergänzen
    $originalKey = $key;
    $i = 1;
    while (in_array($key, $existingKeys)) {
        $key = $originalKey . '_' . $i;
        $i++;
    }

    return $key;
}
?>

<form method="post">
    <?= csrf_field() ?>

    <table class="table">
        <thead>
        <tr>
            <th>Typ (fix)</th>
            <th>Bezeichnung</th>
            <th>Preis (CHF)</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $key => $cat): ?>
            <tr>
                <td><?= esc($key) ?></td>
                <td>
                    <input type="text" name="categories[<?= esc($key) ?>][name]" value="<?= esc($cat['name']) ?>" class="form-control" readonly disabled>
                </td>
                <td>

                    <?php $usedKeys = []; ?>
                    <?php foreach ($cat['options'] as $opt): ?>
                        <?php $optKey = makeOptionKey($opt['label'], $usedKeys); $usedKeys[] = $optKey; ?>
                        <div class="mb-1">
                            <span><?= esc($opt['label']) ?></span>
                            <input type="number" name="categories[<?= esc($key) ?>][options][<?= esc($optKey) ?>][price]" value="<?= esc($opt['price']) ?>" step="0.05" min="0" class="form-control" style="width:100px; display:inline-block; margin-left:5px;">
                            <input type="hidden" name="categories[<?= esc($key) ?>][options][<?= esc($optKey) ?>][label]" value="<?= esc($opt['label']) ?>">
                        </div>
                    <?php endforeach; ?>

                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>



    <h3>Rabattregeln</h3>

    <table class="table" id="discount-rules-table">
        <thead>
        <tr>
            <th>Stunden</th>
            <th>Rabatt (%)</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($discountRules)): ?>
            <?php foreach ($discountRules as $i => $rule): ?>
                <tr>
                    <td><input type="number" name="discountRules[<?= $i ?>][hours]" value="<?= esc($rule['hours']) ?>" class="form-control" min="1"></td>
                    <td><input type="number" name="discountRules[<?= $i ?>][discount]" value="<?= esc($rule['discount']) ?>" class="form-control" min="0" max="100"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">✕</button></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        </tbody>
    </table>

    <button type="button" class="btn btn-secondary" id="add-discount-rule">+ Regel hinzufügen</button>




    <button type="submit" class="btn btn-primary">Speichern</button>
</form>



<script>
    document.addEventListener("DOMContentLoaded", function() {
        let table = document.getElementById("discount-rules-table").getElementsByTagName("tbody")[0];
        let addBtn = document.getElementById("add-discount-rule");

        addBtn.addEventListener("click", function() {
            let index = table.rows.length;
            let row = table.insertRow();

            row.innerHTML = `
            <td><input type="number" name="discountRules[${index}][hours]" class="form-control" min="1"></td>
            <td><input type="number" name="discountRules[${index}][discount]" class="form-control" min="0" max="100"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">✕</button></td>
        `;
        });

        table.addEventListener("click", function(e) {
            if (e.target && e.target.classList.contains("remove-row")) {
                e.target.closest("tr").remove();
            }
        });
    });
</script>



<?= $this->endSection() ?>
