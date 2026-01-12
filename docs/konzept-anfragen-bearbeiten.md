# Konzept: Anfragen bearbeiten

**Datum:** 12. Januar 2026
**Status:** Geplant (separates Feature für später)

---

## Ziel

Admins sollen bestehende Offerten-Anfragen direkt im Fluent Form bearbeiten können, ohne dass ein neuer Eintrag erstellt wird.

---

## Aktueller Zustand

- Offerten-Anfragen werden über Fluent Forms erfasst
- Daten werden per Webhook an die MY Umgebung gesendet
- Im Admin können Anfragen nur angesehen, aber nicht bearbeitet werden
- Änderungen müssen manuell in der Datenbank gemacht werden

---

## Gewünschter Ablauf

```
Admin öffnet Offerte #456 im MY-Admin
    ↓
Klickt auf "Im Formular bearbeiten"
    ↓
Browser öffnet das entsprechende Fluent Form im Edit-Modus:
    /elektriker/offerte-elektriker/?fluentform_entry=123&entry_token=xyz
    ↓
Alle Felder sind mit den bestehenden Daten vorausgefüllt
    ↓
Admin ändert z.B. die Beschreibung oder Kontaktdaten
    ↓
Klickt "Speichern"
    ↓
Fluent Forms aktualisiert den bestehenden Eintrag (Entry #123)
    ↓
Webhook sendet aktualisierte Daten an MY Umgebung
    ↓
MY Umgebung erkennt: Das ist ein Update (nicht neu) und aktualisiert Offerte #456
```

---

## Technische Voraussetzungen

### 1. Fluent Forms Entry-ID speichern

Im Webhook muss die `entry_id` von Fluent Forms mitgesendet und in der MY-Datenbank gespeichert werden.

**Webhook-Payload erweitern:**
```json
{
  "entry_id": 123,
  "form_id": 5,
  "vorname": "Max",
  "nachname": "Muster",
  ...
}
```

**Datenbank-Anpassung:**
```sql
ALTER TABLE offers ADD COLUMN fluent_entry_id INT NULL;
```

### 2. Fluent Forms Pro - Entry Edit aktivieren

Fluent Forms Pro bietet eine "Edit Entry" Funktion:
- Einstellung pro Formular aktivieren
- Token-basierte Sicherheit (nur mit gültigem Token bearbeitbar)

**URL-Format:**
```
/formular-seite/?fluentform_entry={entry_id}&entry_token={token}
```

### 3. Edit-URL im MY-Admin generieren

```php
// Beispiel in PHP
$editUrl = $this->generateFluentFormEditUrl($offer->fluent_entry_id);

function generateFluentFormEditUrl($entryId) {
    // Token von Fluent Forms API abrufen oder generieren
    $token = $this->getFluentEntryToken($entryId);

    // Formular-URL basierend auf Branche und Sprache
    $formUrl = $this->getBranchFormUrl($offer->branch, $offer->language);

    return $formUrl . '?fluentform_entry=' . $entryId . '&entry_token=' . $token;
}
```

### 4. Webhook: Update vs. Create unterscheiden

Der Webhook-Handler muss erkennen, ob es ein neuer Eintrag oder ein Update ist:

```php
public function handleWebhook($data) {
    $entryId = $data['entry_id'];

    // Prüfen ob bereits eine Offerte mit dieser Entry-ID existiert
    $existingOffer = $this->offerModel->where('fluent_entry_id', $entryId)->first();

    if ($existingOffer) {
        // UPDATE: Bestehende Offerte aktualisieren
        $this->offerModel->update($existingOffer->id, $data);
    } else {
        // CREATE: Neue Offerte erstellen
        $this->offerModel->insert($data);
    }
}
```

---

## Admin-UI Mockup

```
┌─────────────────────────────────────────────────────────────────────────┐
│ Offerte #456 - Elektriker                                               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ Kontakt: Max Muster                                                     │
│ E-Mail: max@example.com                                                 │
│ Telefon: 079 123 45 67                                                  │
│                                                                         │
│ Beschreibung:                                                           │
│ Steckdosen im Wohnzimmer erneuern, 5 Stück                              │
│                                                                         │
│ Status: Verifiziert                                                     │
│ Erstellt: 12.01.2026 14:30                                              │
│                                                                         │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  [Im Formular bearbeiten]    [Status ändern]    [Löschen]               │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Sicherheitsaspekte

| Aspekt | Lösung |
|--------|--------|
| Unbefugter Zugriff auf Edit-URL | Token-basierte Authentifizierung (Fluent Forms Pro) |
| Token-Gültigkeit | Tokens sollten zeitlich begrenzt sein oder einmalig verwendbar |
| Admin-Only | Edit-Button nur für eingeloggte Admins sichtbar |
| Audit-Trail | Änderungen protokollieren (wer, wann, was geändert) |

---

## Offene Fragen

1. **Token-Verwaltung:** Wie generiert/speichert Fluent Forms die Edit-Tokens?
2. **Bilder:** Können hochgeladene Bilder im Edit-Modus angezeigt/geändert werden?
3. **Audit-Log:** Sollen Änderungen protokolliert werden?
4. **Benachrichtigung:** Soll der Kunde informiert werden wenn seine Anfrage geändert wird?

---

## Abhängigkeiten

- Fluent Forms Pro Lizenz (für Entry Edit Feature)
- Webhook-Anpassung (Entry-ID mitsenden)
- Datenbank-Migration (fluent_entry_id Spalte)

---

## Nächste Schritte

1. Prüfen ob Fluent Forms Pro bereits lizenziert ist
2. Entry Edit Feature in Fluent Forms testen
3. Webhook um entry_id erweitern
4. Datenbank-Migration erstellen
5. Admin-UI Button implementieren

