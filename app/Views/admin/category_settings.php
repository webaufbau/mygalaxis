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
    <button type="submit" class="btn btn-primary">Speichern</button>
</form>


<?= $this->endSection() ?>
