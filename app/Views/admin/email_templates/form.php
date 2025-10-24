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

                <?php $isNewTemplate = empty($template['id']); ?>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <button class="btn btn-link text-white text-decoration-none w-100 text-start p-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSettings" aria-expanded="<?= $isNewTemplate ? 'true' : 'false' ?>">
                            <h5 class="mb-0">
                                Template Einstellungen
                                <i class="bi bi-chevron-down float-end"></i>
                            </h5>
                        </button>
                    </div>
                    <div class="collapse <?= $isNewTemplate ? 'show' : '' ?>" id="collapseSettings">
                        <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="offer_type" class="form-label">
                                    <i class="bi bi-tag"></i> Branche <span class="text-danger">*</span>
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
                                <label for="subtype" class="form-label">
                                    <i class="bi bi-diagram-3"></i> Unterkategorie
                                </label>
                                <select class="form-select" id="subtype" name="subtype">
                                    <option value="">Alle (Standard)</option>
                                    <!-- Options werden dynamisch via JavaScript geladen -->
                                </select>
                                <small class="form-text text-muted">
                                    Optional: Wählen Sie eine spezifische Unterkategorie. "Alle" bedeutet das Template gilt für alle Unterkategorien dieser Branche.
                                </small>
                            </div>
                        </div>

                        <div class="row">
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
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <button class="btn btn-link text-white text-decoration-none w-100 text-start p-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmailText" aria-expanded="false">
                            <h5 class="mb-0">
                                <i class="bi bi-envelope"></i> E-Mail Inhalt (Header/Text)
                                <i class="bi bi-chevron-down float-end"></i>
                            </h5>
                        </button>
                    </div>
                    <div class="collapse" id="collapseEmailText">
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Hinweis:</strong> Dies ist der statische Email-Text (Begruessung, Erklaerungen, etc.).
                                <br>
                                <strong>Wichtig:</strong> Verwende <code>{{FIELD_DISPLAY}}</code> um die Feld-Darstellung einzubinden.
                            </div>
                            <div class="alert alert-secondary">
                                <i class="bi bi-search"></i>
                                <strong>Im Editor suchen:</strong><br>
                                <small>
                                    <kbd>Ctrl+F</kbd> (Windows/Linux) oder <kbd>Cmd+F</kbd> (Mac) → Suchfeld oeffnen<br>
                                    <kbd>Ctrl+H</kbd> (Windows/Linux) oder <kbd>Cmd+Alt+F</kbd> (Mac) → Ersetzen-Dialog<br>
                                    <kbd>Enter</kbd> → Naechster Treffer | <kbd>Shift+Enter</kbd> → Vorheriger Treffer | <kbd>Esc</kbd> → Schliessen
                                </small>
                            </div>
                            <div class="mb-3">
                                <label for="body_template" class="form-label">
                                    <i class="bi bi-code"></i> Email-Text HTML <span class="text-danger">*</span>
                                </label>
                                <textarea id="body_template"
                                          name="body_template"
                                          required><?= old('body_template', $template['body_template'] ?? '') ?></textarea>
                                <small class="form-text text-muted">
                                    HTML-Code mit Shortcodes wie {field:vorname}, {site_name}, <strong>{{FIELD_DISPLAY}}</strong>, etc.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <button class="btn btn-link text-white text-decoration-none w-100 text-start p-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFieldDisplay" aria-expanded="<?= $isNewTemplate ? 'false' : 'true' ?>">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul"></i> Feld-Darstellung (wiederverwendbar)
                                <i class="bi bi-chevron-down float-end"></i>
                            </h5>
                        </button>
                    </div>
                    <div class="collapse <?= $isNewTemplate ? '' : 'show' ?>" id="collapseFieldDisplay">
                        <div class="card-body">
                            <div class="alert alert-success">
                                <i class="bi bi-lightbulb"></i>
                                <strong>Wiederverwendbar:</strong> Dieses Template wird verwendet fuer:
                                <ul class="mb-0 mt-2">
                                    <li>✅ E-Mail Template (via <code>{{FIELD_DISPLAY}}</code>)</li>
                                    <li>✅ Firmen Details Backend (Offerte-Ansicht)</li>
                                </ul>
                            </div>
                            <div class="alert alert-secondary">
                                <i class="bi bi-search"></i>
                                <strong>Im Editor suchen:</strong><br>
                                <small>
                                    <kbd>Ctrl+F</kbd> (Windows/Linux) oder <kbd>Cmd+F</kbd> (Mac) → Suchfeld oeffnen<br>
                                    <kbd>Ctrl+H</kbd> (Windows/Linux) oder <kbd>Cmd+Alt+F</kbd> (Mac) → Ersetzen-Dialog<br>
                                    <kbd>Enter</kbd> → Naechster Treffer | <kbd>Shift+Enter</kbd> → Vorheriger Treffer | <kbd>Esc</kbd> → Schliessen
                                </small>
                            </div>
                            <div class="mb-3">
                                <label for="field_display_template" class="form-label">
                                    <i class="bi bi-code-square"></i> Feld-Darstellung Template
                                </label>
                                <textarea id="field_display_template"
                                          name="field_display_template"><?= old('field_display_template', $template['field_display_template'] ?? '') ?></textarea>
                                <small class="form-text text-muted">
                                    HTML mit Bedingungen wie [if field:xyz] und Bildern. Siehe Beispiel rechts.
                                </small>
                            </div>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Tipp:</strong> Wenn leer gelassen, wird automatisch <code>[show_all]</code> verwendet.
                            </div>
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
Grosse Wohnung!
[/if]</code>
                                        <small>Zeigt nur wenn Bedingung erfüllt</small>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="insertShortcode('[if field:anzahl_zimmer > 3]\nGrosse Wohnung!\n[/if]')">
                                            <i class="bi bi-clipboard"></i> Einfügen
                                        </button>
                                    </div>

                                    <div class="shortcode-item mb-3">
                                        <code class="d-block bg-light p-2 rounded mb-1 small">[if field:material == Holz]
Material ist Holz
[else]
Anderes Material: {field:andere_material}
[/if]</code>
                                        <small>Mit [else] für Alternative</small>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="insertShortcode('[if field:material == Holz]\nMaterial ist Holz\n[else]\nAnderes Material: {field:andere_material}\n[/if]')">
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

                                    <div class="alert alert-info p-2 small mb-3">
                                        <i class="bi bi-info-circle"></i> <strong>Conditional Groups:</strong><br>
                                        Bedingte Felder (z.B. Bodenplatten Vorplatz) werden automatisch intelligent dargestellt.
                                        Die Regeln sind in <code>app/Config/FieldDisplayRules.php</code> definiert.
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
                                        <small>Zeigt alle Felder inkl. conditional groups (nur nicht-leere)</small>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-1" onclick="insertShortcode('[show_all exclude=&quot;email,phone,terms&quot;]')">
                                            <i class="bi bi-clipboard"></i> Einfügen
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Feld-Darstellung Beispiel -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFieldDisplay">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFieldDisplay">
                                    Feld-Darstellung Beispiel
                                </button>
                            </h2>
                            <div id="collapseFieldDisplay" class="accordion-collapse collapse" data-bs-parent="#shortcodeAccordion">
                                <div class="accordion-body">
                                    <p class="small"><strong>Für "Feld-Darstellung":</strong></p>
                                    <pre class="bg-light p-2 rounded small" style="font-size: 10px; max-height: 300px; overflow-y: auto;"><code>&lt;ul&gt;
[if field:bodenplatten_typ_a_d == Ja]
  &lt;li&gt;
    &lt;img src="https://...bodenplatten_typ_a_d.jpg"&gt;
    &lt;br&gt;&lt;strong&gt;Typ a-d:&lt;/strong&gt;
    {field:bodenplatten_typ_a_d_waehlen}
  &lt;/li&gt;
[/if]

[if field:reinigungsart]
  &lt;li&gt;&lt;strong&gt;Reinigung:&lt;/strong&gt;
  {field:reinigungsart}&lt;/li&gt;
[/if]
&lt;/ul&gt;</code></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Beispiel Template -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingExample">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample">
                                    Email-Text Beispiel
                                </button>
                            </h2>
                            <div id="collapseExample" class="accordion-collapse collapse" data-bs-parent="#shortcodeAccordion">
                                <div class="accordion-body">
                                    <p class="small"><strong>Für "Email-Text":</strong></p>
                                    <pre class="bg-light p-2 rounded small" style="font-size: 10px; max-height: 300px; overflow-y: auto;"><code>&lt;h2&gt;Vielen Dank!&lt;/h2&gt;

&lt;p&gt;Hallo {field:vorname} {field:nachname},&lt;/p&gt;

&lt;div class="highlight"&gt;
  &lt;p&gt;Vielen Dank für Ihre Anfrage über {site_name}.&lt;/p&gt;
&lt;/div&gt;

&lt;h3&gt;Ihre Angaben&lt;/h3&gt;
{{FIELD_DISPLAY}}</code></pre>
                                    <p class="small text-muted mt-2">
                                        <code>{{FIELD_DISPLAY}}</code> wird automatisch durch die "Feld-Darstellung" ersetzt.
                                    </p>
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
// Variable um zu tracken welches Textarea gerade fokussiert ist
let lastFocusedTextarea = 'body_template';

// Track welches Textarea fokussiert wurde
document.addEventListener('DOMContentLoaded', function() {
    const bodyTemplate = document.getElementById('body_template');

    if (bodyTemplate) {
        bodyTemplate.addEventListener('focus', function() {
            lastFocusedTextarea = 'body_template';
        });
    }
});

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

/* Custom Shortcode Highlighting */
.cm-shortcode-if-open {
    color: #9c27b0;
    font-weight: bold;
}

.cm-shortcode-if-close {
    color: #9c27b0;
    font-weight: bold;
}

.cm-shortcode-field {
    color: #2196f3;
    font-weight: 600;
}

.cm-shortcode-command {
    color: #4caf50;
    font-weight: bold;
}

.cm-shortcode-placeholder {
    color: #ff5722;
    font-weight: bold;
    background-color: #fff3e0;
    padding: 2px 4px;
    border-radius: 3px;
}

/* CodeMirror Editor Styling */
.CodeMirror {
    border: none;
    border-radius: 0.375rem;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', monospace !important;
    font-size: 13px !important;
    line-height: 1.6 !important;
}

.CodeMirror-scroll {
    min-height: auto;
}

#body-editor-wrapper .CodeMirror,
#editor-wrapper .CodeMirror {
    height: auto;
}

</style>

<!-- CodeMirror CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/eclipse.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/dialog/dialog.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/search/matchesonscrollbar.min.css">

<!-- CodeMirror JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/mode/overlay.min.js"></script>
<!-- Search Addons -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/search/searchcursor.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/search/search.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/dialog/dialog.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/search/matchesonscrollbar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/scroll/annotatescrollbar.min.js"></script>

<script>
// Define custom mode for shortcodes
CodeMirror.defineMode("shortcode-html", function(config, parserConfig) {
    const htmlMode = CodeMirror.getMode(config, "htmlmixed");

    return CodeMirror.overlayMode(htmlMode, {
        token: function(stream, state) {
            // Match [if field:xyz] or [if field:xyz == value]
            if (stream.match(/\[if\s+field:[a-zA-Z0-9_-]+(?:\s*(?:>|<|>=|<=|==|!=)\s*[^\]]+)?\]/)) {
                return "shortcode-if-open";
            }
            // Match [else]
            if (stream.match(/\[else\]/)) {
                return "shortcode-if-open";
            }
            // Match [/if]
            if (stream.match(/\[\/if\]/)) {
                return "shortcode-if-close";
            }
            // Match {field:xyz}
            if (stream.match(/\{field:[a-zA-Z0-9_-]+(?:\|[^\}]+)?\}/)) {
                return "shortcode-field";
            }
            // Match [show_all ...]
            if (stream.match(/\[show_all(?:[^\]]+)?\]/)) {
                return "shortcode-command";
            }
            // Match [show_field ...]
            if (stream.match(/\[show_field(?:[^\]]+)?\]/)) {
                return "shortcode-command";
            }
            // Match {{FIELD_DISPLAY}}
            if (stream.match(/\{\{[A-Z_]+\}\}/)) {
                return "shortcode-placeholder";
            }

            stream.next();
            return null;
        }
    });
});

// Initialize CodeMirror editors
document.addEventListener('DOMContentLoaded', function() {
    // Initialize body_template editor
    const bodyTextarea = document.getElementById('body_template');
    let bodyEditor = null;

    if (bodyTextarea) {
        bodyEditor = CodeMirror.fromTextArea(bodyTextarea, {
            mode: 'shortcode-html',
            theme: 'eclipse',
            lineNumbers: true,
            lineWrapping: true,
            indentUnit: 4,
            tabSize: 4,
            indentWithTabs: false,
            autofocus: false,
            extraKeys: {
                'Tab': function(cm) {
                    cm.replaceSelection('    ', 'end');
                },
                'Ctrl-F': 'findPersistent',
                'Cmd-F': 'findPersistent',
                'Ctrl-H': 'replace',
                'Cmd-Alt-F': 'replace'
            }
        });

        bodyEditor.setSize(null, '400px');

        bodyEditor.on('change', function() {
            bodyTextarea.value = bodyEditor.getValue();
        });

        bodyEditor.on('focus', function() {
            lastFocusedTextarea = 'body_template';
            window.bodyCodeEditor = bodyEditor;
        });

        window.bodyCodeEditor = bodyEditor;

        // Refresh editor when collapse is shown
        const collapseEmailText = document.getElementById('collapseEmailText');
        if (collapseEmailText) {
            collapseEmailText.addEventListener('shown.bs.collapse', function() {
                if (bodyEditor) {
                    bodyEditor.refresh();
                }
            });
        }
    }

    // Initialize field_display_template editor
    const fieldTextarea = document.getElementById('field_display_template');
    if (fieldTextarea) {
        const fieldEditor = CodeMirror.fromTextArea(fieldTextarea, {
            mode: 'shortcode-html',
            theme: 'eclipse',
            lineNumbers: true,
            lineWrapping: true,
            indentUnit: 4,
            tabSize: 4,
            indentWithTabs: false,
            autofocus: false,
            extraKeys: {
                'Tab': function(cm) {
                    cm.replaceSelection('    ', 'end');
                },
                'Ctrl-F': 'findPersistent',
                'Cmd-F': 'findPersistent',
                'Ctrl-H': 'replace',
                'Cmd-Alt-F': 'replace'
            }
        });

        fieldEditor.setSize(null, '500px');

        fieldEditor.on('change', function() {
            fieldTextarea.value = fieldEditor.getValue();
        });

        fieldEditor.on('focus', function() {
            lastFocusedTextarea = 'field_display_template';
            window.fieldCodeEditor = fieldEditor;
        });
    }
});

// Update insertShortcode to work with CodeMirror
function insertShortcode(shortcode) {
    // Check if we're using CodeMirror
    if (lastFocusedTextarea === 'field_display_template' && window.fieldCodeEditor) {
        const doc = window.fieldCodeEditor.getDoc();
        const cursor = doc.getCursor();
        doc.replaceRange(shortcode, cursor);
        window.fieldCodeEditor.focus();
        return;
    }

    if (lastFocusedTextarea === 'body_template' && window.bodyCodeEditor) {
        const doc = window.bodyCodeEditor.getDoc();
        const cursor = doc.getCursor();
        doc.replaceRange(shortcode, cursor);
        window.bodyCodeEditor.focus();
        return;
    }

    // Fallback to regular textarea (shouldn't happen)
    const textarea = document.getElementById(lastFocusedTextarea);
    if (!textarea) {
        return;
    }

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;

    textarea.value = text.substring(0, start) + shortcode + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + shortcode.length;
}

// Subtype Handling
(function() {
    const subtypeConfig = <?= json_encode(config('OfferSubtypes')->getSubtypesForType('')) ?>;
    const subtypeLabels = <?= json_encode(config('OfferSubtypes')->getSubtypeLabels()) ?>;
    const subtypeMapping = <?= json_encode(config('OfferSubtypes')->subtypeToTypeMapping) ?>;

    const offerTypeSelect = document.getElementById('offer_type');
    const subtypeSelect = document.getElementById('subtype');
    const currentSubtype = '<?= old('subtype', $template['subtype'] ?? '') ?>';

    function updateSubtypeOptions() {
        const selectedType = offerTypeSelect.value;

        // Clear all options
        subtypeSelect.innerHTML = '';

        // Add "Alle" option first
        const alleOption = document.createElement('option');
        alleOption.value = '';
        alleOption.textContent = 'Alle (Standard)';
        subtypeSelect.appendChild(alleOption);

        // Find all subtypes for this offer_type
        const subtypes = [];
        for (const [subtype, type] of Object.entries(subtypeMapping)) {
            if (type === selectedType) {
                subtypes.push(subtype);
            }
        }

        // Add options for each subtype
        subtypes.forEach(subtype => {
            const option = document.createElement('option');
            option.value = subtype;
            option.textContent = subtypeLabels[subtype] || subtype;
            subtypeSelect.appendChild(option);
        });

        // Set the selected value (after all options are added)
        if (currentSubtype && currentSubtype !== '') {
            subtypeSelect.value = currentSubtype;
        } else {
            subtypeSelect.value = ''; // Explicitly select "Alle"
        }

        // Show/hide subtype field based on whether there are subtypes
        const subtypeContainer = subtypeSelect.closest('.col-md-6');
        if (subtypes.length > 0) {
            subtypeContainer.style.display = 'block';
        } else {
            subtypeContainer.style.display = 'none';
            subtypeSelect.value = ''; // Always set to "Alle" if no subtypes
        }
    }

    // Update on page load
    updateSubtypeOptions();

    // Update when offer_type changes
    offerTypeSelect.addEventListener('change', updateSubtypeOptions);
})();
</script>

<?= $this->endSection() ?>
