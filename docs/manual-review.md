# Manuelle Anfragen-Prüfung

## Übersicht

Das System ermöglicht eine manuelle Prüfung aller eingehenden Anfragen bevor sie an Firmen weitergeleitet werden. Ein Admin kann jede Anfrage bearbeiten, Preise anpassen und erst nach Freigabe werden die Firmen benachrichtigt.

## Aktivierung

In den Site-Einstellungen (`/admin/settings`) oder direkt in `writable/config/site_settings.json`:

```json
{
  "manualOfferReviewEnabled": true
}
```

## Ablauf

### 1. Kunde stellt Anfrage
- Kunde füllt Formular aus und verifiziert sich per SMS
- Kunde erhält E-Mail: "Danke, wir prüfen Ihre Anfrage"
- Anfrage landet im Status `pending_review`
- **Keine automatische Benachrichtigung an Firmen**

### 2. Admin prüft Anfrage
- Admin öffnet `/admin/offers/pending`
- Sieht alle ausstehenden Anfragen als Karten
- Kann für jede Anfrage:
  - Preis anpassen
  - Interne Notizen hinzufügen
  - Hinweise für den Kunden schreiben
  - Als Testanfrage markieren
  - Firmen in der Nähe suchen (PLZ + Radius)
  - Alle Formularfelder bearbeiten (inkl. Branche ändern)

### 3. Freigabe
- Admin klickt "Freigeben & Versenden"
- Bestätigungs-Modal zeigt Zusammenfassung
- Nach Bestätigung:
  - Kunde erhält E-Mail: "Ihre Anfrage wurde an Firmen gesendet"
  - Alle passenden Firmen erhalten die Anfrage

## Admin-Seiten

### Pending Review Liste
**URL:** `/admin/offers/pending`

- Zeigt alle Anfragen mit Status `pending_review`
- Karten-Layout mit:
  - Kopfzeile: Typ, ID, Ort, Alter, Preis
  - Kundeninfo: Name, E-Mail, Telefon
  - Schnellbearbeitung: Preis, Testflag, Notizen
  - Firmensuche: PLZ eingeben, Radius wählen, Suchen
  - Freigabe-Button
- Paginierung (10 pro Seite)

### Einzelne Anfrage bearbeiten
**URL:** `/admin/offers/edit/:id`

- Vollständige Bearbeitung aller Felder
- Branche/Typ ändern
- Alle Formularfelder editieren
- Firmensuche in Sidebar
- Freigabe-Button

## Testanfragen

### Markierung
- Checkbox "Testanfrage" bei jeder Anfrage
- Gelbe Hervorhebung in der Liste
- Badge mit Flask-Icon

### Verhalten
- **Testanfragen** werden NUR an **Test-Firmen** gesendet
- **Normale Anfragen** werden NUR an **normale Firmen** gesendet (Test-Firmen werden übersprungen)

### Test-Firma markieren
- Im User-Profil (`/admin/user/:id`) Checkbox "Test-Firma"
- Oder direkt in der Datenbank: `users.is_test = 1`

## Datenbank-Felder

### Tabelle `offers`
| Feld | Typ | Beschreibung |
|------|-----|--------------|
| `admin_notes` | TEXT | Interne Notizen (nur für Admin) |
| `customer_hint` | TEXT | Hinweis für Kunden |
| `custom_price` | DECIMAL(10,2) | Angepasster Preis |
| `is_test` | TINYINT(1) | Testanfrage-Flag |
| `approved_at` | DATETIME | Zeitpunkt der Freigabe |
| `approved_by` | INT | User-ID des Admins |

### Tabelle `users`
| Feld | Typ | Beschreibung |
|------|-----|--------------|
| `is_test` | TINYINT(1) | Test-Firma Flag |

## E-Mail-Templates

### Kunde: Anfrage eingegangen
**Template:** `emails/offer_pending_review.php`
**Betreff:** "Ihre Anfrage wird geprüft"

Wird gesendet wenn manuelle Prüfung aktiv ist, direkt nach SMS-Verifizierung.

### Kunde: Anfrage freigegeben
**Template:** `emails/offer_approved.php`
**Betreff:** "Ihre Anfrage wurde an Firmen gesendet"

Wird nach Admin-Freigabe gesendet.

## Firmensuche

### Funktionsweise
1. PLZ eingeben (Standard: PLZ der Anfrage)
2. Radius wählen (10/20/50/100 km)
3. "Suchen" klicken
4. Zeigt alle Firmen im Umkreis sortiert nach Distanz

### Technische Umsetzung
- Haversine-Formel für Distanzberechnung
- Nutzt `zipcodes`-Tabelle mit Koordinaten
- AJAX-Endpoint: `/admin/offers/search-companies?zipcode=4053&radius=20`

## Code-Referenzen

### Controller
- `app/Controllers/Admin/Offer.php`
  - `pendingReview()` - Liste der ausstehenden Anfragen
  - `editOffer($id)` - Einzelne Anfrage bearbeiten
  - `updateOffer($id)` - Änderungen speichern
  - `approveOffer($id)` - Freigabe
  - `searchCompanies()` - AJAX Firmensuche

### Views
- `app/Views/admin/offers_pending_review.php` - Übersichtsliste
- `app/Views/admin/offer_edit.php` - Bearbeitungsseite

### Libraries
- `app/Libraries/OfferNotificationSender.php` - Firmen-Benachrichtigung
- `app/Libraries/ZipcodeService.php` - PLZ-Radius-Suche

### Helpers
- `app/Helpers/email_template_helper.php`
  - `sendOfferPendingReviewEmail()` - "Wird geprüft" Mail
  - `sendOfferApprovedEmail()` - "Wurde gesendet" Mail

## Migrationen

```
2025-12-01-150000_AddManualReviewFieldsToOffers.php
2025-12-01-150100_AddIsTestToUsers.php
```

## Zukünftige Erweiterungen (Phase 2)

- Excel-Import für nicht-registrierte Firmen
- Akquise-E-Mails an neue Firmen senden
- Zusätzliche Branchen zu einer Anfrage hinzufügen
