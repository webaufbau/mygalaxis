<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2>Gespeicherte Zahlungsmethoden</h2>

<?php if(session()->getFlashdata('message')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
<?php endif; ?>

<a href="<?= site_url('finance/userpaymentmethods/add') ?>" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#paymentModal">Neue Zahlungsmethode hinzufügen</a>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>Zahlungsmethode</th>
        <th>Details</th>
        <th>Platform</th>
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
                if ($method['payment_method_code'] === 'saferpay' && isset($data['alias_id'])) {
                    ?>
                    <i class="bi bi-credit-card text-primary"></i>
                    <span class="ms-2">
                        <?= esc($data['card_brand'] ?? 'Kreditkarte') ?>
                        <?= isset($data['card_masked']) ? esc($data['card_masked']) : '' ?>
                    </span>
                    <?php if (isset($data['card_exp_month']) && isset($data['card_exp_year'])): ?>
                        <br>
                        <small class="text-muted">Gültig bis: <?= str_pad($data['card_exp_month'], 2, '0', STR_PAD_LEFT) ?>/<?= $data['card_exp_year'] ?></small>
                    <?php endif; ?>
                    <?php
                } elseif ($method['payment_method_code'] === 'paypal' && isset($data['email'])) {
                    ?>
                    <i class="bi bi-paypal text-primary"></i>
                    <span class="ms-2"><?= esc($data['email']) ?></span>
                    <?php
                } elseif ($method['payment_method_code'] === 'creditcard' && isset($data['last4'])) {
                    ?>
                    <i class="bi bi-credit-card text-primary"></i>
                    <span class="ms-2">**** **** **** <?= esc($data['last4']) ?></span>
                    <?php
                } elseif ($method['payment_method_code'] === 'twint' && isset($data['phone'])) {
                    ?>
                    <i class="bi bi-phone text-success"></i>
                    <span class="ms-2"><?= esc($data['phone']) ?></span>
                    <?php
                } else {
                    echo '<pre>' . esc(json_encode($data, JSON_PRETTY_PRINT)) . '</pre>';
                }
                ?>

            </td>
            <td>
                <?php if (!empty($method['platform'])): ?>
                    <span class="badge bg-secondary">
                        <?php
                        // Zeige schönen Namen für Platform
                        $platformName = match($method['platform']) {
                            'my_offertenschweiz_ch' => 'Offertenschweiz',
                            'my_offertenheld_ch' => 'Offertenheld',
                            'my_renovo24_ch' => 'Renovo24',
                            default => $method['platform']
                        };
                        echo esc($platformName);
                        ?>
                    </span>
                <?php else: ?>
                    <small class="text-muted">-</small>
                <?php endif; ?>
            </td>
            <td>
                <a href="<?= site_url('finance/userpaymentmethods/delete/'.$method['id']) ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Wirklich löschen?');">
                    Löschen
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Zahlungsmethode hinzufügen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schliessen"></button>
            </div>
            <div class="modal-body">
                <?php echo view('finance/add_user_payment_method'); ?>
            </div>
        </div>
    </div>
</div>


<?= $this->endSection() ?>
