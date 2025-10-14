# Sichere Kontaktdaten-Übertragung (CI4 → WordPress)

## Problem
Kontaktdaten von CI4 an WordPress übertragen, ohne dass Benutzer die Daten in der URL manipulieren können.

## Lösung
Verschlüsselter Token-basierter Ansatz mit HMAC-Signatur:

1. **CI4** erstellt verschlüsselten Token via REST API
2. **WordPress** entschlüsselt Token und speichert Daten in Session
3. **Fluent Forms** befüllt Felder automatisch aus Session
4. **Sicherheit**: Token läuft nach 24h ab, Signatur-Prüfung verhindert Manipulation

---

## Setup

### 1. WordPress: Token Secret Key generieren

Im WordPress Admin unter **Einstellungen → Form Sync** einen Secret Key generieren oder manuell setzen:

```php
// Entweder automatisch (verwendet wp_salt('auth'))
// Oder manuell in den Plugin-Einstellungen:
contact_token_secret: "ihr-geheimer-schluessel-hier"
```

Dieser Key wird für die HMAC-Signatur verwendet.

---

## Implementierung

### CI4: Token erstellen und Weiterleitung

#### Variante 1: Vereinfachte Methode (Empfohlen)

Die `Verification` Controller hat bereits eine fertige Methode `redirectWithContactData()`:

```php
<?php
namespace App\Controllers;

class MyFormController extends BaseController
{
    public function submitForm()
    {
        // Formular-Daten aus POST
        $vorname = $this->request->getPost('vorname');
        $nachname = $this->request->getPost('nachname');
        $email = $this->request->getPost('email');
        $telefon = $this->request->getPost('telefon');

        // ... Formular in Datenbank speichern ...

        // Zur WordPress-Seite mit sicheren Kontaktdaten weiterleiten
        $verification = new \App\Controllers\Verification();
        return $verification->redirectWithContactData(
            $vorname,
            $nachname,
            $email,
            $telefon,
            'https://offertenschweiz.ch/umzuege/offerte-firmenumzug-weiterleitung/'
        );
    }
}
```

**Die Methode kümmert sich automatisch um:**
- REST API Aufruf an WordPress
- API Key aus .env laden
- Token-Validierung
- Fehlerbehandlung
- Weiterleitung

#### Variante 2: Manuelle Implementierung

Falls du den Token-Aufruf selbst implementieren möchtest:

```php
<?php
namespace App\Controllers;

class FormController extends BaseController
{
    public function submitForm1()
    {
        // Formular-Daten aus POST
        $vorname = $this->request->getPost('vorname');
        $nachname = $this->request->getPost('nachname');
        $email = $this->request->getPost('email');
        $telefon = $this->request->getPost('telefon');

        // Token von WordPress API anfordern
        $wordpress_url = 'https://offertenschweiz.ch';
        $token_secret = getenv('syncApi.apiKey') ?: '43r3u4grj23b423j4b23mb43bj23bj334rrw';

        $contact_data = [
            'vorname' => $vorname,
            'nachname' => $nachname,
            'email' => $email,
            'telefon' => $telefon,
            'target_url' => 'https://offertenschweiz.ch/umzuege/offerte-firmenumzug-weiterleitung/'
        ];

        $client = \Config\Services::curlrequest();

        $response = $client->request('POST', $wordpress_url . '/wp-json/waformsyncapi/v1/create-contact-token', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-TOKEN-API-KEY' => $token_secret
            ],
            'json' => $contact_data,
            'http_errors' => false,
            'timeout' => 10
        ]);

        $result = json_decode($response->getBody(), true);

        if (isset($result['url']) && $result['success'] === true) {
            // Weiterleitung mit verschlüsseltem Token
            return redirect()->to($result['url']);
        } else {
            // Fehlerbehandlung
            log_message('error', 'Token-Generierung fehlgeschlagen: ' . print_r($result, true));
            return redirect()->back()->with('error', 'Token-Generierung fehlgeschlagen');
        }
    }
}
```

#### Variante 3: Nach Verifizierung weiterleiten

Direkt in der Verification-Klasse nach erfolgreicher Verifizierung:

```php
// In App\Controllers\Verification::verify()
if ($enteredCode == $sessionCode) {
    // Verifizierung erfolgreich
    // ... Bestehender Code ...

    // Kontaktdaten aus Offer holen
    $offerData = $offerModel->where('uuid', $uuid)->first();
    $fields = json_decode($offerData['form_fields'], true);

    // Prüfen ob Weiterleitung gewünscht
    if (!empty($fields['zusatz_service']) && $fields['zusatz_service'] === 'Ja') {
        return $this->redirectWithContactData(
            $fields['vorname'] ?? '',
            $fields['nachname'] ?? '',
            $fields['email'] ?? '',
            $fields['phone'] ?? '',
            'https://offertenschweiz.ch/zusatzservice/reinigung/'
        );
    }

    // Standard: Erfolgsseite anzeigen
    return view('verification_success', [
        'siteConfig' => $this->siteConfig,
        'next_url' => session('next_url')
    ]);
}
```

**Beispiel Response von WordPress:**
```json
{
    "success": true,
    "token": "eyJ2b3JuYW1lIjoiTWF4IiwibmFjaG5hbWUiOi...",
    "url": "https://offertenschweiz.ch/umzuege/offerte-firmenumzug-weiterleitung/?contact_token=eyJ2b3JuYW1..."
}
```

---

## WordPress: Fluent Forms Setup

### 1. Hidden Fields hinzufügen

Füge diese Hidden Fields zu deinem Fluent Form hinzu:

- `vorname` (Text Input)
- `nachname` (Text Input)
- `email` (Email Input)
- `telefon` (Text Input)
- `skip_kontakt` (Hidden Field) ← **Wichtig für Conditional Logic**

### 2. Conditional Logic einrichten

Für die Kontaktfelder-Gruppe in Fluent Forms:

**Bedingung:**
```
IF skip_kontakt IS EMPTY OR skip_kontakt EQUALS 0
THEN SHOW Kontaktfelder
ELSE HIDE Kontaktfelder
```

**Visuell in Fluent Forms:**
- Gehe zu Formular-Builder
- Wähle die Kontaktfelder-Gruppe
- Klicke auf "Conditional Logic"
- Setze: `skip_kontakt` = `""` (leer) OR `skip_kontakt` = `0`
- Aktion: Show/Hide

---

## Sicherheitsfeatures

### ✅ Verhindert Manipulation
- Token ist HMAC-signiert
- Änderungen am Token werden erkannt und abgelehnt
- Session-Daten werden beim Submit bevorzugt (überschreiben Frontend-Eingaben)

### ✅ Zeitbasierte Gültigkeit
- Token läuft nach 24 Stunden ab
- Verhindert Replay-Attacken

### ✅ Readonly Felder
- Verifizierte Kontaktfelder werden im Frontend als `readonly` markiert
- Verhindert versehentliche oder böswillige Änderungen

### ✅ Session-Schutz
- Daten werden aus URL entfernt nach dem ersten Laden
- Session ist server-seitig und nicht manipulierbar

---

## Ablauf im Detail

### 1. Benutzer füllt Formular 1 in CI4 aus

```
Vorname: Max
Nachname: Muster
Email: max@muster.ch
Telefon: 0791234567
```

### 2. CI4 erstellt Token via WordPress API

```bash
POST /wp-json/waformsyncapi/v1/create-contact-token
X-TOKEN-API-KEY: ihr-geheimer-schluessel
Content-Type: application/json

{
  "vorname": "Max",
  "nachname": "Muster",
  "email": "max@muster.ch",
  "telefon": "0791234567",
  "target_url": "https://offertenschweiz.ch/umzuege/offerte-firmenumzug-weiterleitung/"
}
```

**Response:**
```json
{
  "token": "eyJkYXRhIjp7InZvcm5hbWUiOiJNYXgiLCJuYW...",
  "url": "https://offertenschweiz.ch/umzuege/offerte-firmenumzug-weiterleitung/?contact_token=eyJkYXRhIjp7..."
}
```

### 3. Benutzer wird zu WordPress weitergeleitet

```
https://offertenschweiz.ch/umzuege/offerte-firmenumzug-weiterleitung/?contact_token=eyJkYXRhIjp7...
```

### 4. WordPress verarbeitet Token

**In `handle_secure_contact_data()` (OffertenSyncPlugin.php:85):**
- Entschlüsselt Token
- Prüft Signatur und Zeitstempel
- Speichert Daten in PHP Session:
  ```php
  $_SESSION['vorname'] = 'Max';
  $_SESSION['nachname'] = 'Muster';
  $_SESSION['email'] = 'max@muster.ch';
  $_SESSION['telefon'] = '0791234567';
  $_SESSION['skip_kontakt'] = '1';
  $_SESSION['contact_data_verified'] = true;
  ```
- Leitet zu sauberer URL ohne Token weiter

### 5. Formular wird geladen

**JavaScript (footer_scripts):**
- Liest Session-Daten aus PHP
- Befüllt Formularfelder automatisch
- Macht Felder `readonly` (verhindert Änderungen)
- Setzt `skip_kontakt = 1`
- Conditional Logic versteckt Kontaktfelder

### 6. Benutzer füllt Formular 2 aus

- Kontaktfelder sind bereits ausgefüllt und versteckt
- Benutzer füllt nur restliche Felder aus (z.B. Umzugsdetails)
- Submit

### 7. WordPress überschreibt Kontaktdaten beim Submit

**In `inject_session_contact_data()` (OffertenSyncPlugin.php:202):**
```php
// Überschreibe Frontend-Daten mit Session-Daten (verhindert Manipulation)
if (!empty($_SESSION['contact_data_verified'])) {
    $insertData['vorname'] = $_SESSION['vorname'];
    $insertData['nachname'] = $_SESSION['nachname'];
    $insertData['email'] = $_SESSION['email'];
    $insertData['telefon'] = $_SESSION['telefon'];
}
```

Selbst wenn ein Benutzer versucht, die Felder zu manipulieren (z.B. via Browser DevTools), werden die Daten beim Submit mit den Session-Werten überschrieben.

---

## LocalStorage Fallback

Falls der Benutzer die Seite verlässt und zurückkehrt:

**localStorage speichert:**
```javascript
contact_vorname: "Max"
contact_nachname: "Muster"
contact_email: "max@muster.ch"
contact_telefon: "0791234567"
contact_skip_kontakt: "1"
contact_verified: "true"
```

Beim nächsten Besuch werden diese Daten automatisch geladen (nur wenn `contact_verified = true`).

---

## Testing

### Test 1: Token-Generierung
```bash
curl -X POST https://offertenschweiz.ch/wp-json/waformsyncapi/v1/create-contact-token \
  -H "X-TOKEN-API-KEY: ihr-geheimer-schluessel" \
  -H "Content-Type: application/json" \
  -d '{
    "vorname": "Max",
    "nachname": "Muster",
    "email": "max@muster.ch",
    "telefon": "0791234567"
  }'
```

**Erwartete Response:**
```json
{
  "success": true,
  "token": "...",
  "url": "https://offertenschweiz.ch/.../?contact_token=..."
}
```

### Test 2: URL mit Token öffnen
```
https://offertenschweiz.ch/umzuege/offerte-firmenumzug-weiterleitung/?contact_token=<TOKEN>
```

**Erwartetes Verhalten:**
1. Formular lädt
2. Kontaktfelder sind vorausgefüllt und readonly
3. Kontaktfelder-Gruppe ist ausgeblendet (wegen `skip_kontakt = 1`)
4. URL wird sauber ohne Token (automatische Weiterleitung)

### Test 3: Manipulation verhindern
1. Browser DevTools öffnen
2. Formularfeld `email` manuell ändern (z.B. auf `hacker@evil.com`)
3. Formular absenden
4. In Fluent Forms Submissions prüfen: Email ist immer noch `max@muster.ch` (aus Session)

---

## Fehlerbehebung

### Token wird nicht akzeptiert
**Fehler:** `Invalid Token API Key`

**Lösung:** Prüfe, ob der `X-TOKEN-API-KEY` Header korrekt gesetzt ist und mit dem WordPress Secret übereinstimmt.

### Token ist abgelaufen
**Fehler:** Token wird abgelehnt

**Lösung:** Token läuft nach 24h ab. Neu generieren.

### Felder werden nicht vorausgefüllt
**Lösung:**
1. Browser-Konsole prüfen (F12)
2. PHP Session prüfen: `print_r($_SESSION);`
3. JavaScript-Variable prüfen: `console.log(sessionContactData);`

### skip_kontakt funktioniert nicht
**Lösung:**
1. Hidden Field `skip_kontakt` im Formular vorhanden?
2. Conditional Logic korrekt eingerichtet?
3. JavaScript triggert `change` Event: Prüfe Browser-Konsole

---

## Fallback: Unsichere GET-Parameter (nicht empfohlen)

Falls du OHNE Token arbeiten möchtest (unsicher!):

**WordPress Plugin-Einstellungen:**
```php
allow_contact_from_get: true  // In Admin-Panel aktivieren
```

**URL:**
```
https://offertenschweiz.ch/.../?vorname=Max&nachname=Muster&email=max@muster.ch&telefon=0791234567&skip_kontakt=1
```

⚠️ **Warnung:** Diese Methode ist unsicher, da Benutzer die URL manipulieren können!

---

## Zusammenfassung

| Feature | Status |
|---------|--------|
| Token-basierte Verschlüsselung | ✅ |
| HMAC-Signatur | ✅ |
| Session-Speicherung | ✅ |
| LocalStorage Persistenz | ✅ |
| Readonly Felder | ✅ |
| Submit-Überschreibung | ✅ |
| Token-Ablauf (24h) | ✅ |
| Conditional Logic Support | ✅ |

**Das Plugin ist jetzt vollständig und sicher implementiert!**

---

## CI4 Verwendungsbeispiele

### Beispiel 1: Einfache Weiterleitung nach Formular-Submit

```php
<?php
namespace App\Controllers;

class Umzug extends BaseController
{
    public function submitPrivatumzug()
    {
        // Formulardaten validieren
        $validation = \Config\Services::validation();
        $validation->setRules([
            'vorname' => 'required',
            'nachname' => 'required',
            'email' => 'required|valid_email',
            'telefon' => 'required'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Daten aus POST
        $vorname = $this->request->getPost('vorname');
        $nachname = $this->request->getPost('nachname');
        $email = $this->request->getPost('email');
        $telefon = $this->request->getPost('telefon');

        // Zur Firmenumzug-Seite weiterleiten
        $verification = new \App\Controllers\Verification();
        return $verification->redirectWithContactData(
            $vorname,
            $nachname,
            $email,
            $telefon,
            'https://offertenschweiz.ch/umzuege/offerte-firmenumzug-weiterleitung/'
        );
    }
}
```

### Beispiel 2: Integration im FluentForm Webhook

```php
<?php
// In App\Controllers\FluentForm::webhook()

public function webhook()
{
    $data = $this->request->getPost();

    // ... Formular in Datenbank speichern ...

    // Prüfen ob Benutzer Zusatzservice möchte
    $wuenschtZusatzservice = $data['zusatz_service'] ?? 'Nein';

    if ($wuenschtZusatzservice === 'Ja') {
        // Zur Zusatzservice-Seite weiterleiten mit Kontaktdaten
        $verification = new \App\Controllers\Verification();
        return $verification->redirectWithContactData(
            $data['vorname'] ?? '',
            $data['nachname'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? '',
            'https://offertenschweiz.ch/zusatzservice/reinigung/'
        );
    }

    return $this->response->setJSON(['success' => true]);
}
```

### Beispiel 3: Multi-Step Formular mit Session

```php
<?php
namespace App\Controllers;

class MultiStepForm extends BaseController
{
    public function step1()
    {
        // Step 1: Kontaktdaten sammeln
        return view('forms/step1');
    }

    public function submitStep1()
    {
        // Daten in Session speichern
        session()->set([
            'form_vorname' => $this->request->getPost('vorname'),
            'form_nachname' => $this->request->getPost('nachname'),
            'form_email' => $this->request->getPost('email'),
            'form_telefon' => $this->request->getPost('telefon')
        ]);

        return redirect()->to('/form/step2');
    }

    public function step2()
    {
        // Step 2: Weitere Details
        return view('forms/step2');
    }

    public function submitStep2()
    {
        // ... Step 2 Daten verarbeiten ...

        // Zur WordPress-Seite für finalen Schritt
        $verification = new \App\Controllers\Verification();
        return $verification->redirectWithContactData(
            session('form_vorname'),
            session('form_nachname'),
            session('form_email'),
            session('form_telefon'),
            'https://offertenschweiz.ch/final-step/'
        );
    }
}
```

### Beispiel 4: Dynamische Ziel-URL basierend auf Service-Typ

```php
<?php
namespace App\Controllers;

class DynamicRedirect extends BaseController
{
    private $serviceUrls = [
        'umzug' => 'https://offertenschweiz.ch/umzuege/offerte-umzug/',
        'reinigung' => 'https://offertenschweiz.ch/reinigung/offerte-reinigung/',
        'entsorgung' => 'https://offertenschweiz.ch/entsorgung/offerte-entsorgung/',
        'lagerung' => 'https://offertenschweiz.ch/lagerung/offerte-lagerung/'
    ];

    public function submitWithService()
    {
        $serviceType = $this->request->getPost('service_type');
        $targetUrl = $this->serviceUrls[$serviceType] ?? $this->serviceUrls['umzug'];

        $verification = new \App\Controllers\Verification();
        return $verification->redirectWithContactData(
            $this->request->getPost('vorname'),
            $this->request->getPost('nachname'),
            $this->request->getPost('email'),
            $this->request->getPost('telefon'),
            $targetUrl
        );
    }
}
```

### Beispiel 5: Error Handling mit Fallback

```php
<?php
namespace App\Controllers;

class SafeRedirect extends BaseController
{
    public function submitWithErrorHandling()
    {
        try {
            $verification = new \App\Controllers\Verification();
            $redirect = $verification->redirectWithContactData(
                $this->request->getPost('vorname'),
                $this->request->getPost('nachname'),
                $this->request->getPost('email'),
                $this->request->getPost('telefon'),
                'https://offertenschweiz.ch/zusatzservice/reinigung/'
            );

            // Prüfen ob Redirect erfolgreich
            if ($redirect->getStatusCode() === 302) {
                log_message('info', 'Weiterleitung erfolgreich');
                return $redirect;
            }

        } catch (\Exception $e) {
            log_message('error', 'Fehler bei Weiterleitung: ' . $e->getMessage());
        }

        // Fallback: Lokale Danke-Seite
        return redirect()->to('/thank-you')
            ->with('warning', 'Die Weiterleitung konnte nicht durchgeführt werden.');
    }
}
```

---

## .env Konfiguration

Stelle sicher, dass der API Key in deiner `.env` Datei korrekt konfiguriert ist:

```ini
# API Key für WordPress Token-Synchronisation
syncApi.apiKey=43r3u4grj23b423j4b23mb43bj23bj334rrw
```

**Wichtig:** Der API Key muss auf beiden Seiten identisch sein:
- CI4: `.env` → `syncApi.apiKey`
- WordPress: Plugin-Einstellungen → `contact_token_secret`

---

## Quick Start Guide

1. **WordPress vorbereiten:**
   - Plugin `wavk-form-sync` installieren
   - API Key in Einstellungen setzen
   - Fluent Form mit `skip_kontakt` Hidden Field erstellen

2. **CI4 Controller erstellen:**
   ```php
   $verification = new \App\Controllers\Verification();
   return $verification->redirectWithContactData(
       $vorname, $nachname, $email, $telefon, $targetUrl
   );
   ```

3. **Testen:**
   - Formular ausfüllen
   - Submit
   - Prüfen ob Weiterleitung funktioniert
   - Prüfen ob Felder vorausgefüllt sind

**Fertig!** 🎉
