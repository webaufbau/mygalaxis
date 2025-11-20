# Offene Aufgaben aus todos.csv

## NOCH OFFEN / UNKLAR

### Zeile 2: Verwaltungsseite für Administrator neu gestalten ⚠️
**Status: TEILWEISE ERLEDIGT / UNKLAR**

**Beschreibung:**
12.11.25 Video 1 mit Fotos - Die Verwaltungsseite als Administrator neu gestalten mit den entsprechenden neuen Links, damit alle Daten von der Firma ersichtlich sind.

**Aktueller Stand:**
- Admin User Detail Seite hat Tab-Struktur (Firmendaten, Anfragen, Branchen, Regionen, Finanzen, Bewertungen, Agenda, Notizen)
- Commit 3708b3f hat umfangreiche Tab-Struktur implementiert

**Was fehlt möglicherweise:**
- Ohne das ursprüngliche Video 1 zu sehen, ist unklar ob das Design/Layout den Anforderungen entspricht
- Eventuell sind zusätzliche Links oder Daten-Ansichten gewünscht

**Nächster Schritt:**
Video 1 vom 12.11.25 prüfen und mit aktueller Implementierung vergleichen

---

### Zeile 3: Verwaltungsseiten für Firmen anpassen ⚠️
**Status: TEILWEISE ERLEDIGT / UNKLAR**

**Beschreibung:**
12.11.25 Video 2 mit Fotos - Die Verwaltungsseiten von den Firmen entsprechend anpassen. Diese werden dann in den Verwaltungsseiten beim Administrator übernommen.

**Aktueller Stand:**
- Firmen-Verwaltungsseiten haben: Angebote, Filter, Finanzen, Agenda, Mein Konto, Bewertungen
- Finance-Seite wurde komplett neu gestaltet (Commit 3708b3f)

**Was fehlt möglicherweise:**
- Spezifische Anpassungen aus Video 2
- Synchronisation zwischen Firmen-Ansicht und Admin-Ansicht

**Nächster Schritt:**
Video 2 vom 12.11.25 prüfen

---

### Zeile 5: Video 10.11 16:51 (1:21) ❓
**Status: UNKLAR**

**Beschreibung:**
Plattform und ergänzt und Filter eingebaut

**Problem:**
Anmerkung Vincent: "Könnten Sie mir genau sagen um welches Video es sich handelt. d.h. die Länge in Minuten und Sekunden"

**Nächster Schritt:**
Klärung welches Video gemeint ist - eventuell bereits durch Zeile 26/27/30 abgedeckt

---

### Zeile 7: Video 10.11 17:20 (1:04) ⚠️
**Status: TEILWEISE ERLEDIGT**

**Beschreibung:**
Gekaufte Anfragen Plattform ergänzt, Name Angebot ergänzt, Anzahl Datensätze filtern, Suchfeld

**Aktueller Stand:**
- ✅ Plattform ergänzt (siehe Zeile 26)
- ✅ Filter für Anzahl Datensätze (Statistik-Filter, Zeile 27)
- ❓ Name Angebot ergänzt - UNKLAR was genau gemeint ist
- ❓ Suchfeld - UNKLAR ob zusätzlich zu Filtern ein Suchfeld gewünscht ist

**Nächster Schritt:**
Klären: Was bedeutet "Name Angebot ergänzt"? Wo soll ein Suchfeld ergänzt werden?

---

### Zeile 9: Video Fall Nummer 1 ⚠️
**Status: TEILWEISE ERLEDIGT**

**Beschreibung:**
Bei Firmendetailsansicht im Admin ergänzen alle Infos zum User dargestellt als Tabs, Notizen System eingebaut

**Aktueller Stand:**
- ✅ Tab-System implementiert in admin/user_detail.php (Firmendaten, Anfragen, Branchen, Regionen, Finanzen, Bewertungen, Agenda, Notizen)
- ❓ Notizen System - Muss geprüft werden ob vollständig implementiert

**Anmerkung Vincent vom 13.11.:**
"Habe jetzt geprüft, bei mir als Admin support@galaxisgroup.ch sehe ich keine Änderungen als Admin."

**Nächster Schritt:**
- Prüfen ob Notizen-System funktioniert
- Mit Vincent testen ob die Tab-Ansicht nun sichtbar ist
- Route: /admin/user/detail/{id}?model=user

---

### Zeile 11: Fall Nummer 2 Ergänzung ❌
**Status: NICHT ERLEDIGT**

**Beschreibung:**
1. Agenda kompakter darstellen, damit es Platz auf kleinen Bildschirmen hat
2. Bemerkungen mit Bezeichnung ergänzen

**Was fehlt:**
- Responsive Design für Agenda auf kleinen Bildschirmen
- Bemerkungen-Feld mit Bezeichnung

**Nächster Schritt:**
- Agenda-Ansicht auf mobile Geräte optimieren
- Bemerkungen-Feature in Agenda hinzufügen

---

### Zeile 12: Änderung der Mail Adresse ✅ → ERLEDIGT
(bereits als erledigt markiert, siehe Zeile 6 und 12 sind das gleiche)

---

### Zeile 13: Fall Nummer 3 ❓
**Status: UNKLAR / MÖGLICHERWEISE ERLEDIGT**

**Beschreibung:**
E-Mail kommt nicht automatisch ins Formularfeld bei Firma (ist jetzt umbenannt, damit die Firma die Möglichkeit hat nach außen öffentlich eine andere Mailadresse anzugeben)

**Aktueller Stand:**
- E-Mail-Änderungs-Feature wurde implementiert (Zeile 6)
- Es gibt nun company_email (öffentlich) und email_text (Login)

**Nächster Schritt:**
Prüfen ob die Unterscheidung zwischen Login-Email und öffentlicher Firmen-Email korrekt funktioniert

---

### Zeile 14: Anpassung noch bei Finanzseite ⚠️
**Status: TEILWEISE ERLEDIGT**

**Beschreibung:**
13.11., 16.56, Video 4 - Anpassungen zu der Finanzseite und Integrierung von Monatsrechnungen

**Aktueller Stand:**
- ✅ Finanzseite komplett neu gestaltet
- ✅ Sektion "Ausgestellte Monatsrechnungen" vorhanden (finance.php Zeile 450-483)
- ❓ Unklar ob Monatsrechnungen-System vollständig funktioniert

**Nächster Schritt:**
- Prüfen ob Monatsrechnungen generiert und angezeigt werden
- Video 4 vom 13.11. zur Verifizierung prüfen

---

### Zeile 17: Neue Karte hinterlegen / Karte ändern ❌
**Status: NICHT VOLLSTÄNDIG ERLEDIGT**

**Beschreibung:**
13.11, 17.19 Uhr, Video 6 - Bei der Finanzseite das Formular anpassen, wo man die Daten der Karte sehen oder auch ändern kann, oder eine 2. Karte als Option hinterlegen kann.

**Aktueller Stand:**
- ✅ Karte ändern ist möglich (Button vorhanden)
- ✅ Karten-Brand wird angezeigt (TWINT, Mastercard, Visa)
- ❌ ZWEITE KARTE hinterlegen noch nicht möglich
- ❌ Karten-Details (letzte 4 Ziffern, Ablaufdatum) werden nicht angezeigt

**Was fehlt:**
- Möglichkeit zur Hinterlegung einer 2. Zahlungskarte
- Anzeige der Kartendetails (nicht nur Brand)

**Nächster Schritt:**
- System erweitern für mehrere Karten pro User
- Stripe Payment Methods anzeigen mit Details

---

### Zeile 18: Papierkorb erstellen ❌
**Status: NICHT ERLEDIGT**

**Beschreibung:**
13.11., 17.37 - Papierkorb erstellen, wo man alle Seiten/Daten ansehen kann die gelöscht wurden, von allen Admin Benutzern. Nur Hauptadmin kann definitiv löschen.

**Was fehlt:**
- Soft-Delete System für alle Admin-Bereiche
- Papierkorb-Ansicht in Admin-Bereich
- Berechtigungssystem: Alle sehen, nur Hauptadmin löscht definitiv

**Nächster Schritt:**
- Soft-Deletes aktivieren in allen Models
- Papierkorb-Controller und View erstellen
- Permission-System für endgültiges Löschen

---

### Zeile 19: Fall Nummer 4 ⚠️
**Status: MÖGLICHERWEISE ERLEDIGT**

**Beschreibung:**
Finanzenseite ergänzen/unterteilen 1) Gekaufte Anfragen, 2) Gutschriftenübersicht, 3) Ausgestellte Monatsrechnungen

**Aktueller Stand:**
- ✅ Alle 3 Sektionen sind in finance.php implementiert (Zeilen 321-483)
- Gekaufte Anfragen: Zeilen 321-396
- Gutschriftenübersicht: Zeilen 398-448
- Ausgestellte Monatsrechnungen: Zeilen 450-483

**Anmerkung Vincent:**
"Es ist mir nicht ganz klar, was Sie diesen Hinweis sagen möchten"

**Nächster Schritt:**
Mit Vincent klären - scheint bereits erledigt zu sein (Teil von Zeile 10)

---

### Zeile 20: Fall Nummer 4 Ergänzungen ❌
**Status: NICHT ERLEDIGT**

**Beschreibung:**
Admin Bereich Rechnungen hinzufügen mit Angaben der Firmen und Filterung Periode, Plattformen und Firmen

**Was fehlt:**
- Admin-Bereich für Rechnungen-Verwaltung
- Filter nach Periode, Plattform, Firma
- Rechnungen manuell erstellen können

**Nächster Schritt:**
- Controller/View für Admin Rechnungs-Verwaltung erstellen
- Filter-System implementieren
- Rechnungs-Erstellung für Admin

---

### Zeile 21: Fall Nummer 5 ⚠️
**Status: MÖGLICHERWEISE ERLEDIGT**

**Beschreibung:**
Gutschriften / Weiterempfehlung

**Aktueller Stand:**
- ✅ Affiliate-System implementiert (Zeile 15)
- ✅ Gutschriften-Übersicht in Finance-Seite

**Nächster Schritt:**
Mit Vincent klären - scheint Teil von Zeile 15 zu sein

---

### Zeile 22: Fall Nummer 6 ❌
**Status: NICHT ERLEDIGT**

**Beschreibung:**
Möglichkeit zur Zweitkarten Hinterlegung

**Was fehlt:**
Siehe Zeile 17 - gleiche Anforderung

**Nächster Schritt:**
Multi-Card System implementieren

---

### Zeile 23: Fall Nummer 7 ⚠️
**Status: UNKLAR**

**Beschreibung:**
Option Papierkorb: Gelöschte Anfragen einsehen

**Aktueller Stand:**
- Offers-Trash System existiert (Migration: CreateOffersTrashTable.php)
- ❓ Unklar ob vollständig implementiert und für Admin zugänglich

**Nächster Schritt:**
- Prüfen ob Trash-System für Offers funktioniert
- Admin-Ansicht für gelöschte Anfragen erstellen falls noch nicht vorhanden

---

### Zeile 24: Bestätigung erhalten für Firma 1 Test ✅
**Status: ERLEDIGT**

Siehe Zeile 28 - Willkommensmail wurde korrigiert

---

### Zeile 25: Anpassung bei Verwaltungsseite Anfrage ✅
**Status: ERLEDIGT**

Siehe Zeile 29 - Dashboard direkt zu Angeboten

---

### Zeile 31: Video 10 ✅
**Status: ERLEDIGT**

Siehe Zeile 27 - Statistik-Filter vollständig implementiert

---

## ZUSAMMENFASSUNG OFFENE ITEMS

### DEFINITIV OFFEN (5 Items):
1. **Zeile 11** - Agenda kompakter für mobile, Bemerkungen ergänzen
2. **Zeile 17** - Zweite Karte hinterlegen können
3. **Zeile 18** - Papierkorb-System für alle Admin-Bereiche
4. **Zeile 20** - Admin Rechnungs-Verwaltung mit Filtern
5. **Zeile 22** - Zweitkarten (gleich wie Zeile 17)

### ZU KLÄREN / PRÜFEN (9 Items):
1. **Zeile 2** - Admin Verwaltungsseite Design (Video 1 prüfen)
2. **Zeile 3** - Firmen Verwaltungsseiten (Video 2 prüfen)
3. **Zeile 5** - Video 10.11 16:51 (welches Video?)
4. **Zeile 7** - Name Angebot ergänzt, Suchfeld (was genau?)
5. **Zeile 9** - Notizen-System vollständig? Mit Vincent testen
6. **Zeile 13** - Login-Email vs. öffentliche Email (funktioniert das?)
7. **Zeile 14** - Monatsrechnungen vollständig implementiert?
8. **Zeile 19** - Falls Nummer 4 bereits erledigt? (Teil von Zeile 10)
9. **Zeile 23** - Offers-Trash System vollständig?

### MÖGLICHERWEISE BEREITS ERLEDIGT (3 Items):
- **Zeile 19** - Finanzenseite unterteilen (scheint erledigt)
- **Zeile 21** - Gutschriften/Weiterempfehlung (ist Zeile 15)
- **Zeile 23** - Papierkorb Anfragen (Trash-System existiert)

---

## PRIORITÄTEN-VORSCHLAG

### HOHE PRIORITÄT:
1. **Zeile 18** - Papierkorb-System (wichtig für Daten-Verwaltung)
2. **Zeile 20** - Admin Rechnungs-Verwaltung (wichtig für Buchhaltung)
3. **Zeile 11** - Agenda responsive (wichtig für mobile User)

### MITTLERE PRIORITÄT:
4. **Zeile 17/22** - Zweite Karte hinterlegen (Nice-to-have)
5. **Zeile 9** - Notizen-System prüfen/vervollständigen

### KLÄRUNG ERFORDERLICH:
- Zeilen 2, 3, 5, 7, 13, 14 - Videos prüfen / mit Vincent klären
