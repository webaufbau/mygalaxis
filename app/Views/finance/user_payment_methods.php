<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2>Gespeicherte Zahlungsmethoden</h2>

<?php if(session()->getFlashdata('message')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<a href="<?= site_url('finance/userpaymentmethods/add') ?>" class="btn btn-primary mb-3">Neue Zahlungsmethode hinzufügen</a>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>Zahlungsmethode</th>
        <th>Details</th>
        <th>Aktion</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($methods as $method):
        $data = json_decode($method['provider_data'], true);
        ?>
        <tr>
            <td><?= esc($method['payment_method_code']) ?></td>
            <td>
                <?php
                if ($method['payment_method_code'] === 'paypal' && isset($data['email'])) {
                    echo esc($data['email']);
                } elseif ($method['payment_method_code'] === 'creditcard' && isset($data['last4'])) {
                    ?>
                    <span>**** **** **** <?= esc($data['last4']) ?></span>
                    <?php
                } elseif ($method['payment_method_code'] === 'twint' && isset($data['phone'])) {
                    echo esc($data['phone']);
                } else {
                    echo '<pre>' . esc(json_encode($data, JSON_PRETTY_PRINT)) . '</pre>';
                }
                ?>

            </td>
            <td>
                <a href="<?= site_url('finance/delete_user_payment_method/'.$method['id']) ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Wirklich löschen?');">
                    Löschen
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>


<?= $this->endSection() ?>
