# Konzept: Neuer Formular-Flow für Offerten-Anfragen

**Datum:** 12. Januar 2026
**Von:** Vince
**An:** Thomas Forster (Formularersteller), Herr Bade (Auftraggeber)
**Betreff:** Vorschlag zur Umsetzung des neuen Multi-Branchen-Flows

---

## Ausgangslage

Herr Forster hat mir mitgeteilt, dass der Ablauf der Formular-Ausfüllung grundlegend geändert werden soll. Dieses Dokument beschreibt meinen Vorschlag, wie wir das technisch umsetzen können.

---

## Vergleich: Alter vs. Neuer Flow

### Bisheriger Ablauf (ALT)

```
Kunde öffnet z.B. offertenschweiz.ch/elektriker/
    ↓
Füllt Elektriker-Formular aus (alle Steps inkl. Kontakt, Termin, AGB)
    ↓
Letzter Step: "Benötigst du noch weitere Dienstleister?"
    → Nein: Direkt zur Telefon-Verifizierung
    → Ja (z.B. Umzug): Formular wird gespeichert, Weiterleitung zum Umzug-Formular
    ↓
Beim nächsten Formular: Kontaktdaten werden übersprungen (aus vorherigem übernommen)
    ↓
Das Spiel wiederholt sich, bis der Kunde "Nein" wählt
    ↓
Erst dann: Verifizierung für ALLE Formulare
```

**Probleme mit dem alten Flow:**
- "Weitere Dienstleistungen" kommt erst am Ende jedes Formulars
- Kunde muss sich durch jedes Formular komplett durchklicken
- Kontakt/Termin wird teilweise mehrfach abgefragt oder umständlich übersprungen

---

### Neuer Ablauf (GEWÜNSCHT)

```
Kunde öffnet z.B. offertenschweiz.ch/elektriker/
    ↓
Klickt auf "Jetzt Offerte anfordern"
    ↓
[1] START-SCREEN: Branchen- und Projekt-Auswahl (NUR EINMAL am Anfang!)
    - Elektriker ist bereits vorausgewählt
    - Kunde kann weitere Branchen ankreuzen (z.B. Boden, Heizung)
    - Kunde kann auch Projekte ankreuzen (z.B. Bad-Sanierung)
    - Klickt "Weiter"
    ↓
[2] ELEKTRIKER-FORMULAR: Nur die branchenspezifischen Fragen
    - Art des Objekts (Wohnung, Haus, etc.)
    - Welche Arbeiten? (Neubau, Renovierung, etc.)
    - Beschreibung
    - Bild-Upload
    - Klickt "Weiter"
    ↓
[3] BODEN-FORMULAR: Nur die branchenspezifischen Fragen (falls ausgewählt)
    ↓
[4] HEIZUNG-FORMULAR: Nur die branchenspezifischen Fragen (falls ausgewählt)
    ↓
[5] TERMIN: Wann sollen die Arbeiten beginnen? Flexibilität? (NUR EINMAL am Ende!)
    ↓
[6] KONTAKT: Vorname, Nachname, E-Mail, Telefon, Adresse (NUR EINMAL am Ende!)
    ↓
[7] AGB + ABSENDEN
    ↓
[8] VERIFIZIERUNG: SMS-Code eingeben
    ↓
FERTIG: Alle Anfragen werden an die entsprechenden Firmen weitergeleitet
```

**Vorteile des neuen Flows:**
- Kunde wählt ZUERST alle benötigten Dienstleistungen aus
- Kontakt und Termin werden NUR EINMAL am Schluss abgefragt
- Übersichtlicher und schneller für den Kunden
- Weniger Abbrüche, weil der Kunde den Umfang von Anfang an sieht

---

## Mein Vorschlag: Aufgabenverteilung

Um den neuen Flow umzusetzen, schlage ich folgende Aufteilung vor:

| Komponente | Wo | Begründung |
|------------|-----|------------|
| **Start-Screen** (Branchen/Projekt-Auswahl) | **MY Umgebung** (my.offertenschweiz.ch) | Zentrale Steuerung, muss nicht in jedem WordPress-Formular dupliziert werden |
| **Branchenspezifische Fragen** | **WordPress** (Fluent Forms) | Bereits gebaut, visueller Editor, mehrsprachig (DE/EN/FR/IT) |
| **Termin-Abfrage** | **MY Umgebung** | Nur einmal am Ende, nicht in jedem Formular |
| **Kontakt-Abfrage** | **MY Umgebung** | Nur einmal am Ende |
| **AGB + Absenden** | **MY Umgebung** | Nur einmal am Ende |
| **Verifizierung** | **MY Umgebung** | Bereits implementiert |

---

## Detaillierter Ablauf (Schritt für Schritt)

### Schritt 1-2: Einstieg über WordPress

1. Kunde besucht z.B. **offertenschweiz.ch/elektriker/**
2. WordPress zeigt die Landingpage mit Informationen zum Elektriker-Service
3. Kunde klickt auf **"Jetzt Offerte anfordern"**
4. Der Button leitet weiter zu: **my.offertenschweiz.ch/anfrage/start?initial=electrician**

### Schritt 3-4: Start-Screen (MY Umgebung)

5. Die MY Umgebung zeigt den **Branchen- und Projekt-Auswahl-Screen**
6. **Elektriker ist bereits vorausgewählt** (wegen dem Parameter `?initial=electrician`)
7. Kunde sieht alle verfügbaren Branchen und Projekte als Checkboxen
8. Kunde wählt zusätzlich z.B. "Boden" und "Heizung" aus
9. Kunde klickt **"Weiter"**
10. Die MY Umgebung speichert die Auswahl (Elektriker, Boden, Heizung) in einer temporären Session
11. Weiterleitung zum ersten WordPress-Formular: **offertenschweiz.ch/elektriker/offerte-elektriker/?session=abc123&mode=multi**

### Schritt 5-9: Branchenspezifische Formulare (WordPress)

12. Fluent Form zeigt das **Elektriker-Formular** (vereinfacht, ohne Kontakt/Termin)
13. Kunde füllt aus: Art des Objekts, Arbeiten, Beschreibung, Bild
14. Kunde klickt **"Weiter"**
15. Das Formular sendet die Daten an die MY Umgebung
16. Die MY Umgebung speichert die Daten temporär und prüft: Was kommt als nächstes?
17. Nächste Branche ist "Boden" → Weiterleitung zu: **offertenschweiz.ch/bodenleger/offerte-bodenleger/?session=abc123&mode=multi**
18. Gleiches Spiel für Boden-Formular
19. Dann Heizung-Formular
20. Nach dem letzten Branchen-Formular: Weiterleitung zu **my.offertenschweiz.ch/anfrage/abschluss?session=abc123**

### Schritt 10-14: Abschluss (MY Umgebung)

21. Die MY Umgebung zeigt den **Termin-Screen**: Wann sollen die Arbeiten beginnen? Flexibilität?
22. Kunde klickt **"Weiter"**
23. Die MY Umgebung zeigt den **Kontakt-Screen**: Vorname, Nachname, E-Mail, Telefon, Adresse, Erreichbarkeit
24. Kunde klickt **"Weiter"**
25. Die MY Umgebung zeigt **AGB + Datenschutz** mit dem Hinweis zur SMS-Verifizierung
26. Kunde klickt **"Jetzt Offerten anfordern"**
27. Die MY Umgebung erstellt alle Anfragen (Elektriker, Boden, Heizung) in der Datenbank
28. **Verifizierung startet** (SMS-Code oder Anruf)
29. Nach erfolgreicher Verifizierung: Alle Anfragen werden an die entsprechenden Firmen weitergeleitet

---

## Was muss geändert werden?

### A) Änderungen an den WordPress-Formularen (Thomas Forster)

**Für JEDES bestehende Branchen-Formular müssen folgende Steps ENTFERNT werden:**

| ENTFERNEN | BEHALTEN |
|-----------|----------|
| Kontakt-Step (Vorname, Nachname, E-Mail, Telefon, Adresse) | Art des Objekts |
| Termin-Step (Wann sollen Arbeiten beginnen, Flexibilität) | Branchenspezifische Arbeiten |
| "Weitere Dienstleistungen"-Step (Nein/Umzug/Reinigung/etc.) | Beschreibung |
| AGB-Step | Bild-Upload |

**Zusätzlich müssen zwei neue Hidden Fields hinzugefügt werden:**

1. **mode** - Wert leer lassen (wird automatisch per URL gesetzt)
2. **session_id** - Wert leer lassen (wird automatisch per URL gesetzt)

**Die Webhook-URL bleibt gleich:** Die Formulare senden weiterhin an den bestehenden Webhook.

**Die bedingte Weiterleitung (Conditional Redirect) muss ENTFERNT werden.** Die MY Umgebung steuert zukünftig, wohin der Kunde nach dem Absenden geleitet wird.

**Wichtig:** Die Formulare müssen weiterhin in allen 4 Sprachen (DE, EN, FR, IT) verfügbar sein.

---

### B) Änderungen an der MY Umgebung (Vince)

Ich werde folgende neue Komponenten in der MY Umgebung (my.offertenschweiz.ch) erstellen:

**1. Start-Screen (Branchen/Projekt-Auswahl)**
- Neue Seite unter: my.offertenschweiz.ch/anfrage/start
- Zeigt alle verfügbaren Branchen als Checkboxen
- Zeigt alle verfügbaren Projekte als Checkboxen (aus der Admin-Verwaltung)
- Die Branche aus der URL (z.B. `?initial=electrician`) ist bereits vorausgewählt
- Mehrsprachig (DE, EN, FR, IT)

**2. Session-Verwaltung**
- Speichert die ausgewählten Branchen/Projekte temporär
- Merkt sich, welches Formular der Kunde gerade ausfüllt
- Sammelt alle Formulardaten bis zum Abschluss

**3. Abschluss-Screens**
- Termin-Abfrage (Datum, Flexibilität)
- Kontakt-Abfrage (alle Kontaktfelder)
- AGB + Absenden
- Alle drei Screens mehrsprachig (DE, EN, FR, IT)

**4. Angepasste Webhook-Verarbeitung**
- Erkennt ob der Kunde im "Multi-Modus" ist (mehrere Branchen ausgewählt)
- Speichert Daten temporär bis alle Formulare ausgefüllt sind
- Leitet automatisch zum nächsten Formular oder zum Abschluss weiter

**5. Finalisierung**
- Erstellt alle Anfragen in der Datenbank (eine pro ausgewählter Branche/Projekt)
- Startet die SMS-Verifizierung
- Nach Verifizierung: Alle Anfragen werden aktiviert

**6. Admin-Bereich: Branchen-Verwaltung (NEU)**

Im Admin-Bereich der MY Umgebung wird eine neue Seite erstellt, auf der alle Branchen verwaltet werden können:

| Einstellung | Beschreibung |
|-------------|--------------|
| **Sortierung** | Die Reihenfolge, in der die Branchen im Start-Screen angezeigt werden. Per Drag & Drop oder Nummer anpassbar. |
| **Farbe** | Jede Branche kann eine eigene Farbe erhalten (z.B. Elektriker = Gelb, Sanitär = Blau). Diese Farbe wird im Start-Screen als Hintergrund oder Rahmen der Checkbox angezeigt. |
| **Formular-Links je Sprache** | Für jede Branche müssen die URLs zu den WordPress-Formularen hinterlegt werden (DE, EN, FR, IT). |
| **Aktiv/Inaktiv** | Branchen können ein- oder ausgeblendet werden, ohne sie zu löschen. |

**Beispiel der Branchen-Verwaltung im Admin:**

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│ Branchen-Verwaltung                                                    [+ Neue Branche]    │
├─────┬─────────────┬─────────┬─────────────────────────────────────────────────┬────────────┤
│ Nr. │ Name        │ Farbe   │ Formular-URLs                                   │ Status     │
├─────┼─────────────┼─────────┼─────────────────────────────────────────────────┼────────────┤
│ 1   │ Umzug       │ 🟦 Blau │ DE: /umzug/offerte-umzug/                       │ ✅ Aktiv   │
│     │             │         │ EN: /en/moving/moving-quote/                    │            │
│     │             │         │ FR: /fr/demenagement/devis-demenagement/        │            │
│     │             │         │ IT: /it/trasloco/preventivo-trasloco/           │            │
├─────┼─────────────┼─────────┼─────────────────────────────────────────────────┼────────────┤
│ 2   │ Reinigung   │ 🟩 Grün │ DE: /reinigung/offerte-reinigung/               │ ✅ Aktiv   │
│     │             │         │ EN: /en/cleaning/cleaning-quote/                │            │
│     │             │         │ ...                                             │            │
├─────┼─────────────┼─────────┼─────────────────────────────────────────────────┼────────────┤
│ 3   │ Elektriker  │ 🟨 Gelb │ DE: /elektriker/offerte-elektriker/             │ ✅ Aktiv   │
│     │             │         │ ...                                             │            │
├─────┼─────────────┼─────────┼─────────────────────────────────────────────────┼────────────┤
│ ... │ ...         │ ...     │ ...                                             │ ...        │
└─────┴─────────────┴─────────┴─────────────────────────────────────────────────┴────────────┘
```

**Warum ist das wichtig?**
- Der Start-Screen in der MY Umgebung muss wissen, wohin er den Kunden nach der Auswahl leiten soll
- Die Formular-URLs sind je Sprache unterschiedlich (WPML)
- Die Sortierung und Farben können vom Auftraggeber selbst angepasst werden, ohne Programmierung

**7. Admin-Bereich: Projekt-Verwaltung (NEU)**

Projekte sind **Aliase/Shortcuts** zu Branchen-Formularen. Wenn ein Kunde ein Projekt auswählt, wird er zum entsprechenden Branchen-Formular weitergeleitet.

| Einstellung | Beschreibung |
|-------------|--------------|
| **Name** | Anzeigename des Projekts (z.B. "Pool bauen") |
| **Slug** | Technischer Name (z.B. "pool") |
| **Zugewiesene Branche** | Die Branche, zu deren Formular weitergeleitet wird |
| **Sortierung** | Reihenfolge im Start-Screen |
| **Aktiv/Inaktiv** | Ein-/Ausblenden |

**Beispiel der Projekt-Verwaltung im Admin:**

```
┌─────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ Projekt-Verwaltung                                      [Export CSV] [Import CSV] [+ Neues Projekt]     │
├─────┬────────────────┬─────────────────┬──────────────────────┬─────────────┬─────────────┬─────────────┤
│ ⋮⋮  │ Projekt-Name   │ Slug            │ Zugewiesene Branche  │ Formular    │ Status      │ Aktionen    │
├─────┼────────────────┼─────────────────┼──────────────────────┼─────────────┼─────────────┼─────────────┤
│ ⋮⋮  │ Pool bauen     │ pool            │ Maurer (mason)     ▾ │ → Maurer    │ ✅ Aktiv    │ ✎ 🗑        │
├─────┼────────────────┼─────────────────┼──────────────────────┼─────────────┼─────────────┼─────────────┤
│ ⋮⋮  │ Bad-Sanierung  │ bad_sanierung   │ Sanitär (plumbing) ▾ │ → Sanitär   │ ✅ Aktiv    │ ✎ 🗑        │
├─────┼────────────────┼─────────────────┼──────────────────────┼─────────────┼─────────────┼─────────────┤
│ ⋮⋮  │ Wintergarten   │ wintergarten    │ Schreiner (carp.)  ▾ │ → Schreiner │ ✅ Aktiv    │ ✎ 🗑        │
├─────┼────────────────┼─────────────────┼──────────────────────┼─────────────┼─────────────┼─────────────┤
│ ⋮⋮  │ Küche komplett │ kueche          │ Küchenbauer        ▾ │ → Küchenbau │ ✅ Aktiv    │ ✎ 🗑        │
├─────┼────────────────┼─────────────────┼──────────────────────┼─────────────┼─────────────┼─────────────┤
│ ⋮⋮  │ Dachsanierung  │ dachsanierung   │ Dachdecker (roofer)▾ │ → Dachdecker│ ✅ Aktiv    │ ✎ 🗑        │
└─────┴────────────────┴─────────────────┴──────────────────────┴─────────────┴─────────────┴─────────────┘

⋮⋮ = Drag & Drop zum Sortieren
▾  = Dropdown zur Auswahl der zugewiesenen Branche
✎  = Bearbeiten
🗑  = Löschen
```

**Admin-Funktionen für Projekte:**

| Funktion | Beschreibung |
|----------|--------------|
| **Neues Projekt hinzufügen** | Button [+ Neues Projekt] öffnet ein Modal mit Name, Slug, Branche-Dropdown |
| **Sortierung (Drag & Drop)** | Per Drag & Drop können Projekte sortiert werden. Die Reihenfolge bestimmt die Anzeige im Start-Screen |
| **Branche zuweisen** | Jedes Projekt hat ein Dropdown mit allen verfügbaren Branchen. Bei Änderung sofort gespeichert |
| **Bearbeiten** | Name und Slug können jederzeit geändert werden |
| **Löschen** | Mit Sicherheitsabfrage - Projekt wird vollständig gelöscht |
| **Aktivieren/Deaktivieren** | Klick auf Status schaltet zwischen Aktiv/Inaktiv um |
| **Export CSV** | Exportiert alle Projekte als CSV-Datei (Name, Slug, Branche, Sortierung, Status) |
| **Import CSV** | Importiert Projekte aus einer CSV-Datei. Bestehende Projekte können aktualisiert oder übersprungen werden |

**CSV-Format für Import/Export:**

```csv
name;slug;branch_slug;sort_order;active
Pool bauen;pool;mason;1;1
Bad-Sanierung;bad_sanierung;plumbing;2;1
Wintergarten;wintergarten;carpenter;3;1
Küche komplett;kueche;kitchen;4;1
Dachsanierung;dachsanierung;roofer;5;1
```

**Wie Projekte funktionieren:**

```
User wählt im Start-Screen: "Pool bauen" (Projekt)
    ↓
System schaut nach: pool → mason (Maurer)
    ↓
System schaut nach: mason, Sprache DE → /maurer/offerte-maurer/
    ↓
Weiterleitung zu: offertenschweiz.ch/maurer/offerte-maurer/?session=abc123
    ↓
User füllt das Maurer-Formular aus (wie bei normaler Branche)
```

**Vorteile dieser Lösung:**
- Einfach: Projekt ist nur ein Redirect zu einem Branchen-Formular
- Keine zusätzlichen Formulare für Projekte nötig
- Flexibel: Zuweisung kann jederzeit im Admin geändert werden
- Kunden sehen im Start-Screen "Pool bauen" statt "Maurer" (verständlicher)

---

### C) Änderungen an den WordPress-Seiten

#### Landingpages (z.B. offertenschweiz.ch/elektriker/)

**Der "Jetzt Offerte anfordern"-Button muss zu einer neuen URL führen:**

Statt: Direkt zum WordPress-Formular
Neu: **my.offertenschweiz.ch/anfrage/start?initial=electrician**

Der Parameter `initial` gibt an, welche Branche vorausgewählt sein soll:
- Elektriker-Seite: `?initial=electrician`
- Umzug-Seite: `?initial=move`
- Reinigung-Seite: `?initial=cleaning`
- usw.

#### Formular-Seiten (z.B. offertenschweiz.ch/elektriker/offerte-elektriker/)

**Automatische Umleitung wenn kein Session-Parameter vorhanden:**

```
User öffnet: offertenschweiz.ch/elektriker/offerte-elektriker/
    ↓
WordPress prüft: Ist "session" in der URL?
    ↓
NEIN → Automatische Weiterleitung zu: my.offertenschweiz.ch/anfrage/start?initial=electrician
    ↓
JA  → Formular wird normal angezeigt (User kommt vom Start-Screen)
```

**Warum?**
- Der Start-Screen (Branchen/Projekt-Auswahl) ist der EINZIGE Einstiegspunkt
- Niemand soll das Formular direkt aufrufen können ohne vorher die Auswahl zu machen
- Alte/direkte Links werden automatisch zum neuen Flow umgeleitet

**Umsetzung (Vince - im wavk-form-sync Plugin):**

Die automatische Umleitung wird zentral im WordPress-Plugin **wavk-form-sync** umgesetzt. Thomas muss nichts an den einzelnen Seiten ändern.

**Neue Plugin-Einstellung: Formular-URLs mit Branchen-Type**

Da jede Website (offertenschweiz.ch, offertenheld.de, etc.) andere URL-Strukturen haben kann, müssen alle Formular-URLs explizit im Plugin definiert werden:

```
Formular-URL                                    | Branchen-Type | Sprache
─────────────────────────────────────────────────────────────────────────
/elektriker/offerte-elektriker/                 | electrician   | de
/en/electrician/electrician-quote/              | electrician   | en
/fr/electricien/devis-electricien/              | electrician   | fr
/it/elettricista/preventivo-elettricista/       | electrician   | it
/umzug/offerte-umzug/                           | move          | de
/en/moving/moving-quote/                        | move          | en
/reinigung/offerte-reinigung/                   | cleaning      | de
/en/cleaning/cleaning-quote/                    | cleaning      | en
... etc. für alle Branchen und Sprachen
```

**Plugin-Einstellung im Admin (Textarea):**

```
/elektriker/offerte-elektriker/|electrician|de
/en/electrician/electrician-quote/|electrician|en
/fr/electricien/devis-electricien/|electrician|fr
/it/elettricista/preventivo-elettricista/|electrician|it
/umzug/offerte-umzug/|move|de
/en/moving/moving-quote/|move|en
/reinigung/offerte-reinigung/|cleaning|de
... etc.
```

**Wie es funktioniert:**

```
User öffnet: offertenschweiz.ch/elektriker/offerte-elektriker/
    ↓
Plugin prüft: Ist "/elektriker/offerte-elektriker/" in der Liste?
    → JA, gefunden: electrician | de
    ↓
Plugin prüft: Session-Parameter vorhanden?
    → NEIN
    ↓
Plugin leitet um zu:
    my.offertenschweiz.ch/anfrage/start?initial=electrician&lang=de
```

**Beispiele:**

| User öffnet | In Liste gefunden | Umleitung zu |
|-------------|-------------------|--------------|
| `/elektriker/offerte-elektriker/` | electrician, de | `...?initial=electrician&lang=de` |
| `/en/electrician/electrician-quote/` | electrician, en | `...?initial=electrician&lang=en` |
| `/umzug/offerte-umzug/` | move, de | `...?initial=move&lang=de` |
| `/irgendwas/andere-seite/` | NICHT gefunden | Keine Umleitung |

**Vorteile dieser Lösung:**
- 100% flexibel - jede URL kann individuell definiert werden
- Funktioniert für alle Websites mit unterschiedlichen URL-Strukturen
- Neue Formulare können einfach hinzugefügt werden
- Keine automatische Erkennung die fehlschlagen könnte

---

## Offene Fragen zur Klärung

Bevor wir mit der Umsetzung beginnen, sollten folgende Punkte geklärt werden:

1. **Session-Timeout:** Wie lange soll eine angefangene Anfrage gültig bleiben?
   - Vorschlag: 2 Stunden, danach werden temporäre Daten gelöscht

2. **Abbruch:** Was passiert, wenn der Kunde mittendrin abbricht?
   - Vorschlag: Temporäre Daten werden nach dem Timeout automatisch gelöscht

3. **Zurück-Navigation:** Soll der Kunde zwischen den Formularen zurück navigieren können?
   - Vorschlag: Ja, mit einem "Zurück"-Button

4. **Projekte im Start-Screen:** Wie sollen die Projekte angezeigt werden?
   - Option A: Alle Projekte direkt sichtbar
   - Option B: Erst nach Klick auf "Weitere Optionen anzeigen"

5. **Reihenfolge der Formulare:** In welcher Reihenfolge sollen die Branchen-Formulare angezeigt werden, wenn mehrere ausgewählt sind?
   - Vorschlag: In der Reihenfolge wie sie im Start-Screen angeordnet sind

6. **Bestehende Links:** Was passiert mit direkten Links zu den WordPress-Formularen?
   - Vorschlag: Diese funktionieren weiterhin im "Single-Modus" (nur eine Branche)

---

## Zeitplan (Vorschlag)

| Phase | Aufgabe | Verantwortlich |
|-------|---------|----------------|
| 1 | Abstimmungsgespräch zu offenen Fragen | Alle |
| 2 | Anpassung der WordPress-Formulare (Steps entfernen, Hidden Fields hinzufügen) | Thomas Forster |
| 3 | Entwicklung Start-Screen + Session-Verwaltung in MY Umgebung | Vince |
| 4 | Entwicklung Abschluss-Screens (Termin, Kontakt, AGB) in MY Umgebung | Vince |
| 5 | Anpassung der Webhook-Verarbeitung | Vince |
| 6 | Anpassung der WordPress-Landingpages (Button-URLs) | Thomas Forster |
| 7 | Tests auf Testumgebung | Alle |
| 8 | Go-Live | Alle |

---

## Nächste Schritte

1. **Feedback zu diesem Vorschlag** - Bitte teilen Sie mir mit, ob der beschriebene Ablauf Ihren Vorstellungen entspricht
2. **Klärung der offenen Fragen** - Idealerweise in einem kurzen Telefongespräch
3. **Dann:** Start der Umsetzung

Bei Fragen stehe ich gerne zur Verfügung.

Freundliche Grüsse
Vince
