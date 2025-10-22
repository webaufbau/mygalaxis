<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><?= esc($title) ?></h1>
                <a href="/admin/email-templates" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Zurück
                </a>
            </div>

            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Fehler:</strong>
                    <ul class="mb-0">
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?= csrf_field() ?>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Template Einstellungen</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="offer_type" class="form-label">
                                    <i class="bi bi-tag"></i> Offer Type / Branche <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="offer_type" name="offer_type" required>
                                    <?php
                                    $currentValue = old('offer_type', $template['offer_type'] ?? 'default');
                                    foreach ($offerTypes as $key => $label):
                                    ?>
                                        <option value="<?= esc($key) ?>" <?= $currentValue === $key ? 'selected' : '' ?>>
                                            <?= esc($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">
                                    Wählen Sie die Branche für dieses Template aus
                                </small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="language" class="form-label">
                                    <i class="bi bi-translate"></i> Sprache <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="language" name="language" required>
                                    <option value="de" <?= old('language', $template['language'] ?? 'de') === 'de' ? 'selected' : '' ?>>Deutsch (DE)</option>
                                    <option value="fr" <?= old('language', $template['language'] ?? '') === 'fr' ? 'selected' : '' ?>>Français (FR)</option>
                                    <option value="it" <?= old('language', $template['language'] ?? '') === 'it' ? 'selected' : '' ?>>Italiano (IT)</option>
                                    <option value="en" <?= old('language', $template['language'] ?? '') === 'en' ? 'selected' : '' ?>>English (EN)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">
                                <i class="bi bi-envelope"></i> E-Mail Betreff <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="subject"
                                   name="subject"
                                   value="<?= old('subject', $template['subject'] ?? '') ?>"
                                   placeholder="z.B. Ihre Umzugsanfrage bei {site_name}"
                                   required>
                            <small class="form-text text-muted">
                                Sie können Shortcodes verwenden wie {site_name} oder {field:vorname}
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       <?= old('is_active', $template['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    Template aktiv
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                <i class="bi bi-sticky"></i> Interne Notizen
                            </label>
                            <textarea class="form-control"
                                      id="notes"
                                      name="notes"
                                      rows="2"
                                      placeholder="Optionale Notizen für interne Zwecke"><?= old('notes', $template['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">E-Mail Inhalt</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="body_template" class="form-label">
                                <i class="bi bi-code"></i> Template HTML <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control font-monospace"
                                      id="body_template"
                                      name="body_template"
                                      rows="20"
                                      required><?= old('body_template', $template['body_template'] ?? '') ?></textarea>
                            <small class="form-text text-muted">
                                HTML-Code mit Shortcodes. Siehe Hilfe rechts für verfügbare Shortcodes.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-4">
                    <a href="/admin/email-templates" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Abbrechen
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Speichern
                    </button>
                </div>
            </form>
        </div>

        <!-- Shortcode Help Sidebar -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-question-circle"></i> Shortcode Hilfe
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="shortcodeAccordion">
                        <!-- Basis Shortcodes -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingBasic">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBasic">
                                    Basis Shortcodes
                                </button>
                            </h2>
                            <div id="collapseBasic" class="accordion-collapse collapse show" data-bs-parent="#shortcodeAccordion">
                                <div class="accordion-body">
                                    <div class="shortcode-item mb-3">
                                        <code class="d-block bg-light p-2 rounded mb-1">{field:vorname}</code>
                                        <small>Zeigt den Wert des Feldes "vorname" an</small>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="insertShortcode('{field:vorname}')">
                                            <i class="bi bi-clipboard"></i> Einfügen
                                        </button>
                                    </div>

                                    <div class="shortcode-item mb-3">
                                        <code class="d-block bg-light p-2 rounded mb-1">{field:nachname}</code>
                                        <small>Zeigt den Nachnamen an</small>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="insertShortcode('{field:nachname}')">
                                            <i class="bi bi-clipboard"></i> Einfügen
                                        </button>
                                    </div>

                                    <div class="shortcode-item mb-3">
                                        <code class="d-block bg-light p-2 rounded mb-1">{site_name}</code>
                                        <small>Name der Website aus der Config</small>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="insertShortcode('{site_name}')">
                                            <i class="bi bi-clipboard"></i> Einfügen
                                        </button>
                                    </div>

                                    <div class="shortcode-item mb-3">
                                        <code class="d-block bg-light p-2 rounded mb-1">{site_url}</code>
                                        <small>URL der Website</small>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="insertShortcode('{site_url}')">
                                            <i class="bi bi-clipboard"></i> Einfügen
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datum Formatierung -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingDate">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDate">
                                    Datum Formatierung
                                </button>
                            </h2>
                            <div id="collapseDate" class="accordion-collapse collapse" data-bs-parent="#shortcodeAccordion">
                                <div class="accordion-body">
                                    <div class="shortcode-item mb-3">
                                        <code class="d-block bg-light p-2 rounded mb-1">{field:umzugsdatum|date:d.m.Y}</code>
                                        <small>Datum formatiert: 15.12.2025</small>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="insertShortcode('{field:umzugsdatum|date:d.m.Y}')">
                                            <i class="bi bi-clipboard"></i> Einfügen
                                        </button>
                                    </div>

                                    <div class="alert alert-info p-2 small">
                                        <strong>Formate:</strong><br>
                                        d.m.Y → 15.12.2025<br>
                                        d/m/Y → 15/12/2025<br>
                                        Y-m-d → 2025-12-15
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bedingungen -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingConditions">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseConditions">
                                    Bedingungen
                                </button>
                            </h2>
                            <div id="collapseConditions" class="accordion-collapse collapse" data-bs-parent="#shortcodeAccordion">
                                <div class="accordion-body">
                                    <div class="shortcode-item mb-3">
                                        <code class="d-block bg-light p-2 rounded mb-1 small">[if field:umzugsdatum]
Ihr Umzugstermin: {field:umzugsdatum}
[/if]</code>
                                        <small>Zeigt Inhalt nur wenn Feld existiert</small>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="insertShortcode('[if field:umzugsdatum]\nIhr Umzugstermin: {field:umzugsdatum}\n[/if]')">
                                            <i class="bi bi-clipboard"></i> Einfügen
                                        </button>
                                    </div>

                                    <div class="shortcode-item mb-3">
                                        <code class="d-block bg-light p-2 rounded mb-1 small">[if field:anzahl_zimmer > 3]
Große Wohnung!
[/if]</code>
                                        <small>Zeigt nur wenn Bedingung erfüllt</small>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="insertShortcode('[if field:anzahl_zimmer > 3]\nGroße Wohnung!\n[/if]')">
                                            <i class="bi bi-clipboard"></i> Einfügen
                                        </button>
                                    </div>

                                    <div class="alert alert-info p-2 small">
                                        <strong>Operatoren:</strong><br>
                                        &gt; &lt; &gt;= &lt;= == !=
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Feldanzeige -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFields">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFields">
                                    Feldanzeige
                                </button>
                            </h2>
                            <div id="collapseFields" class="accordion-collapse collapse" data-bs-parent="#shortcodeAccordion">
                                <div class="accordion-body">
                                    <div class="alert alert-success p-2 small mb-3">
                                        <i class="bi bi-check-circle"></i> <strong>Automatisches Überspringen:</strong><br>
                                        Wenn ein Feld im Template verwendet wird, aber im aktuellen Formular nicht existiert oder leer ist, wird es automatisch übersprungen und nicht angezeigt.
                                    </div>

                                    <div class="shortcode-item mb-3">
                                        <code class="d-block bg-light p-2 rounded mb-1 small">[show_field name="qm" label="Quadratmeter"]</code>
                                        <small>Zeigt einzelnes Feld mit Label (nur wenn vorhanden)</small>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="insertShortcode('[show_field name=&quot;qm&quot; label=&quot;Quadratmeter&quot;]')">
                                            <i class="bi bi-clipboard"></i> Einfügen
                                        </button>
                                    </div>

                                    <div class="shortcode-item mb-3">
                                        <code class="d-block bg-light p-2 rounded mb-1 small">[show_all exclude="email,phone,terms"]</code>
                                        <small>Zeigt alle Felder außer ausgeschlossene (nur nicht-leere)</small>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="insertShortcode('[show_all exclude=&quot;email,phone,terms&quot;]')">
                                            <i class="bi bi-clipboard"></i> Einfügen
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Beispiel Template -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingExample">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample">
                                    Vollständiges Beispiel
                                </button>
                            </h2>
                            <div id="collapseExample" class="accordion-collapse collapse" data-bs-parent="#shortcodeAccordion">
                                <div class="accordion-body">
                                    <button class="btn btn-success btn-sm w-100 mb-2" onclick="loadExampleTemplate()">
                                        <i class="bi bi-download"></i> Beispiel laden
                                    </button>
                                    <pre class="bg-light p-2 rounded small" style="font-size: 10px; max-height: 300px; overflow-y: auto;"><code>&lt;h2&gt;Vielen Dank!&lt;/h2&gt;

&lt;p&gt;Hallo {field:vorname} {field:nachname},&lt;/p&gt;

&lt;div class="highlight"&gt;
  &lt;p&gt;Vielen Dank für Ihre Anfrage über {site_name}.&lt;/p&gt;
&lt;/div&gt;

[if field:umzugsdatum]
&lt;p&gt;&lt;strong&gt;Ihr Termin:&lt;/strong&gt; {field:umzugsdatum|date:d.m.Y}&lt;/p&gt;
[/if]

&lt;h3&gt;Ihre Angaben&lt;/h3&gt;
&lt;ul&gt;
[show_all exclude="email,phone,terms"]
&lt;/ul&gt;</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function insertShortcode(shortcode) {
    const textarea = document.getElementById('body_template');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;

    textarea.value = text.substring(0, start) + shortcode + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + shortcode.length;
}

function loadExampleTemplate() {
    const exampleTemplate = `<h2>Vielen Dank für Ihre Anfrage!</h2>

<p>Hallo {field:vorname} {field:nachname},</p>

<div class="highlight">
    <p>Vielen Dank für Ihre Anfrage über {site_name}.</p>
    <p>Wir leiten Ihre Anfrage an passende Firmen in Ihrer Region weiter.</p>
    <p>Sie erhalten in Kürze unverbindliche Offerten direkt per E-Mail oder Telefon.</p>
</div>

<h3>So funktioniert es</h3>
<ul>
    <li>Passende Firmen erhalten Ihre Anfrage</li>
    <li>Sie werden innerhalb von 48 Stunden kontaktiert</li>
    <li>Sie vergleichen bis zu 5 kostenlose Offerten</li>
    <li>Sie wählen das beste Angebot aus</li>
</ul>

<p><strong>Wichtig:</strong> Die Offerten sind komplett kostenlos und unverbindlich.</p>

[if field:umzugsdatum]
<p><strong>Gewünschter Termin:</strong> {field:umzugsdatum|date:d.m.Y}</p>
[/if]

<h3>Zusammenfassung Ihrer Angaben</h3>
<ul>
[show_all exclude="terms_n_condition,terms_and_conditions,terms,type,lang,language,csrf_test_name,submit,form_token,__submission,__fluent_form_embded_post_id,_wp_http_referer,form_name,uuid,service_url,uuid_value,verified_method,utm_source,utm_medium,utm_campaign,utm_term,utm_content,referrer,vorname,nachname,email,phone,skip_kontakt,skip_reinigung_umzug"]
</ul>`;

    if (confirm('Möchten Sie das aktuelle Template mit dem Beispiel ersetzen?')) {
        document.getElementById('body_template').value = exampleTemplate;
    }
}
</script>

<style>
.sticky-top {
    z-index: 1020;
}

.shortcode-item code {
    font-size: 0.85rem;
    word-break: break-all;
}

.accordion-button:not(.collapsed) {
    background-color: #e7f3ff;
    color: #0056b3;
}

.font-monospace {
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.9rem;
}
</style>

<?= $this->endSection() ?>
