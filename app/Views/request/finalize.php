<?= $this->extend('layout/minimal') ?>
<?= $this->section('content') ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Fortschrittsanzeige -->
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <?php
                    $steps = [
                        'termin' => 'Termin',
                        'auftraggeber' => 'Auftraggeber',
                        'kontakt' => 'Kontakt',
                        'verify' => 'Bestätigung'
                    ];
                    $stepKeys = array_keys($steps);
                    $currentStepIndex = array_search($step, $stepKeys);
                    ?>
                    <?php foreach ($steps as $key => $label): ?>
                        <?php
                        $stepIndex = array_search($key, $stepKeys);
                        $isActive = ($key === $step);
                        $isDone = $stepIndex < $currentStepIndex;
                        ?>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1
                                <?= $isActive ? 'bg-primary text-white' : ($isDone ? 'bg-success text-white' : 'bg-light text-muted') ?>"
                                 style="width: 32px; height: 32px;">
                                <?= $isDone ? '<i class="bi bi-check"></i>' : ($stepIndex + 1) ?>
                            </div>
                            <div class="small <?= $isActive ? 'fw-bold' : 'text-muted' ?>"><?= $label ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar" style="width: <?= (($currentStepIndex + 1) / count($steps)) * 100 ?>%"></div>
                </div>
            </div>

            <!-- Zusammenfassung der gewählten Dienstleistungen -->
            <div class="alert alert-light mb-4">
                <strong>Gewählte Dienstleistungen:</strong>
                <?php foreach ($sessionData['form_links'] as $i => $link): ?>
                    <span class="badge bg-secondary me-1"><?= esc($link['name']) ?></span>
                <?php endforeach; ?>
            </div>

            <form method="post" action="<?= site_url('/request/save-finalize') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="session" value="<?= esc($sessionId) ?>">
                <input type="hidden" name="step" value="<?= esc($step) ?>">

                <?php if ($step === 'termin'): ?>
                    <!-- SCHRITT: Termin -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Wann soll die Arbeit ausgeführt werden?</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="flexibel" id="flexibel_ja" value="1" checked>
                                    <label class="form-check-label" for="flexibel_ja">
                                        Flexibel / So schnell wie möglich
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="flexibel" id="flexibel_nein" value="0">
                                    <label class="form-check-label" for="flexibel_nein">
                                        Bestimmter Zeitraum
                                    </label>
                                </div>
                            </div>

                            <div id="termin_details" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="datum" class="form-label">Gewünschtes Datum</label>
                                        <input type="date" class="form-control" id="datum" name="datum">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="zeit" class="form-label">Uhrzeit (optional)</label>
                                        <select class="form-select" id="zeit" name="zeit">
                                            <option value="">Egal</option>
                                            <option value="morgens">Morgens (8-12 Uhr)</option>
                                            <option value="nachmittags">Nachmittags (12-17 Uhr)</option>
                                            <option value="abends">Abends (17-20 Uhr)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    document.querySelectorAll('input[name="flexibel"]').forEach(function(radio) {
                        radio.addEventListener('change', function() {
                            document.getElementById('termin_details').style.display =
                                this.value === '0' ? 'block' : 'none';
                        });
                    });
                    </script>

                <?php elseif ($step === 'auftraggeber'): ?>
                    <!-- SCHRITT: Auftraggeber -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Wer ist der Auftraggeber?</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-2" id="card_privat" style="cursor: pointer;">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-person fs-1 text-primary"></i>
                                            <h6 class="mt-2">Privatperson</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-2" id="card_firma" style="cursor: pointer;">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-building fs-1 text-primary"></i>
                                            <h6 class="mt-2">Firma / Verwaltung</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="typ" id="typ" value="privat" required>

                            <div id="firma_details" style="display: none;" class="mt-3">
                                <label for="firma" class="form-label">Firmenname</label>
                                <input type="text" class="form-control" id="firma" name="firma" placeholder="Firma XY GmbH">
                            </div>
                        </div>
                    </div>

                    <script>
                    document.getElementById('card_privat').addEventListener('click', function() {
                        document.getElementById('typ').value = 'privat';
                        this.classList.add('border-primary');
                        document.getElementById('card_firma').classList.remove('border-primary');
                        document.getElementById('firma_details').style.display = 'none';
                    });
                    document.getElementById('card_firma').addEventListener('click', function() {
                        document.getElementById('typ').value = 'firma';
                        this.classList.add('border-primary');
                        document.getElementById('card_privat').classList.remove('border-primary');
                        document.getElementById('firma_details').style.display = 'block';
                    });
                    // Default
                    document.getElementById('card_privat').classList.add('border-primary');
                    </script>

                <?php elseif ($step === 'kontakt'): ?>
                    <!-- SCHRITT: Kontaktdaten -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Ihre Kontaktdaten</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="vorname" class="form-label">Vorname *</label>
                                    <input type="text" class="form-control" id="vorname" name="vorname" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nachname" class="form-label">Nachname *</label>
                                    <input type="text" class="form-control" id="nachname" name="nachname" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">E-Mail *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefon" class="form-label">Telefon *</label>
                                    <input type="tel" class="form-control" id="telefon" name="telefon" required>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="strasse" class="form-label">Strasse & Nr. *</label>
                                    <input type="text" class="form-control" id="strasse" name="strasse" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="plz" class="form-label">PLZ *</label>
                                    <input type="text" class="form-control" id="plz" name="plz" required>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="ort" class="form-label">Ort *</label>
                                    <input type="text" class="form-control" id="ort" name="ort" required>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($step === 'verify'): ?>
                    <!-- SCHRITT: Verifikation -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Anfrage bestätigen</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                Bitte bestätige deine Anfrage. Du erhältst eine SMS oder E-Mail mit einem Bestätigungscode.
                            </div>

                            <!-- Zusammenfassung -->
                            <h6>Zusammenfassung:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Dienstleistungen:</strong>
                                    <?php foreach ($sessionData['form_links'] as $link): ?>
                                        <?= esc($link['name']) ?><?= $link !== end($sessionData['form_links']) ? ', ' : '' ?>
                                    <?php endforeach; ?>
                                </li>
                                <?php if (!empty($sessionData['kontakt'])): ?>
                                <li><strong>Kontakt:</strong> <?= esc($sessionData['kontakt']['vorname'] . ' ' . $sessionData['kontakt']['nachname']) ?></li>
                                <li><strong>Adresse:</strong> <?= esc($sessionData['kontakt']['strasse'] . ', ' . $sessionData['kontakt']['plz'] . ' ' . $sessionData['kontakt']['ort']) ?></li>
                                <?php endif; ?>
                            </ul>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="agb" required>
                                <label class="form-check-label" for="agb">
                                    Ich akzeptiere die <a href="#" target="_blank">AGB</a> und <a href="#" target="_blank">Datenschutzbestimmungen</a>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between mt-4">
                    <?php if ($step !== 'termin'): ?>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Zurück
                        </a>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <?= $step === 'verify' ? 'Anfrage absenden' : 'Weiter' ?>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<?= $this->endSection() ?>
