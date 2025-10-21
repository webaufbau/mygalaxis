# Kombi-Anfragen: Mögliche Umsetzungsvarianten

## Hintergrund

Bis vor dem Gespräch zu viert gab es nur eine Kombi-Variante. Das heisst, die beiden Offerten (Umzug + Reinigung) konnten **nicht einzeln** gekauft werden. Das hatte ich damals so verstanden und umgesetzt.

Nach dem Gespräch habe ich diese in **2 einzelne Offerten** unterteilt, sodass Firmen nur Umzug oder nur Reinigung kaufen können.

Bevor ich das nun erneut als Kombi-Lösung programmiere, möchte ich euch verschiedene Umsetzungsmöglichkeiten zur Entscheidung vorlegen:

---

## **Variante 1: Nur Kombi-Paket (ursprüngliche Variante)**

### Wie funktioniert es:
- Es wird nur **eine einzige Offerte** erstellt mit Typ `move_cleaning`
- Beim Kauf werden **beide Dienstleistungen** (Umzug + Reinigung) an unterschiedliche Firmen weitergeleitet
- Einzelkauf ist **nicht möglich** - nur als Paket
- Maximal 4 Käufe pro Kombi-Offerte

### Vorteile:
- ✅ Sehr einfach zu implementieren
- ✅ Klare Preisgestaltung (nur Kombi-Preis mit Rabatten)
- ✅ Keine Konflikte zwischen Einzel- und Kombi-Käufen
- ✅ Funktioniert bereits (wie vor dem Gespräch)

### Nachteile:
- ❌ Keine Flexibilität - Firmen können nicht nur Umzug oder nur Reinigung kaufen
- ❌ Einschränkung für Firmen, die nur an einem Bereich interessiert sind
- ❌ Geringere Verkaufschancen

### Bewertung:
Eine Bewertung pro Kombi-Kauf

### Verwaltungsbox:
Eine Offerte mit beiden Services sichtbar

---

## **Variante 2: Einzelofferten + separate Kombi-Offerte**

### Wie funktioniert es:
- Zwei einzelne Offerten (Umzug + Reinigung) bleiben bestehen
- Eine **dritte Offerte** mit Typ `move_cleaning` wird zusätzlich erstellt (Kombi-Preis)
- Alle drei können **unabhängig voneinander** bis zu 4x verkauft werden
- Insgesamt theoretisch bis zu 12 Verkäufe möglich (4x Umzug + 4x Reinigung + 4x Kombi)

### Vorteile:
- ✅ Maximale Flexibilität für Firmen
- ✅ Klare Trennung der Verkaufsoptionen
- ✅ Einfache Implementierung in bestehender Struktur

### Nachteile:
- ❌ Theoretisch bis zu 12 Verkäufe (möglicherweise zu viel?)
- ❌ Komplexität beim Statusmanagement

### Bewertung:
Je nach gekaufter Offerte (1 Bewertung für Umzug, 1 für Reinigung, oder 2 Bewertungen beim Kombi-Kauf)

### Verwaltungsbox:
Drei separate Einträge (Umzug, Reinigung, Kombi)

---

## **Variante 3: Einzelofferten + Kombi mit geteiltem Counter**

### Wie funktioniert es:
- Zwei einzelne Offerten + eine Kombi-Offerte existieren
- Alle drei **teilen sich einen gemeinsamen Käuferzähler**
- Maximal **4 Verkäufe insgesamt** (egal ob Umzug, Reinigung oder Kombi)
- Beispiel: 2x Umzug einzeln + 1x Kombi = 3/4 Slots belegt

### Vorteile:
- ✅ Flexibilität + klare Limitierung
- ✅ Verhindert "Überkauf"
- ✅ Faire Verteilung zwischen Einzel- und Kombi-Käufen

### Nachteile:
- ❌ Komplexe Logik beim Kaufprozess
- ❌ Wenn jemand nur Umzug kauft, blockiert das einen Slot für potenzielle Kombi-Käufe

### Bewertung:
Je nach gekaufter Offerte

### Verwaltungsbox:
Drei Einträge, aber mit gemeinsamem Status-Indikator

---

## **Variante 4: Dynamische Kombi-Anzeige in der Offertenliste**

### Wie funktioniert es:
- Nur **zwei echte Offerten** existieren in der Datenbank (Umzug + Reinigung)
- In der Offertenliste wird **dynamisch eine dritte Option** angezeigt: "Beide zum Kombi-Preis"
- Beim Kauf der dynamischen Kombi wird bei **beiden Einzelofferten je 1 Slot** abgezogen
- Kein separater Datenbank-Eintrag für die Kombi

### Vorteile:
- ✅ Flexibilität ohne zusätzliche Datenbank-Einträge
- ✅ Automatische Anzeige nur wenn beide Services im Filter
- ✅ Klare Slot-Verwaltung (max. 4 pro Einzelofferte)

### Nachteile:

#### Problem 1: Komplexe Implementierung bei Rabatten
- Welcher Rabatt gilt? Der von Umzug oder von Reinigung?
- Beide Offerten können unterschiedlich alt sein → unterschiedliche Rabattstufen
- **Mögliche Lösung:** Rabatt der **älteren** Offerte anwenden?

#### Problem 2: Verwaltungsbox
- Es gibt keinen echten `move_cleaning` Typ in der Datenbank
- In der Verwaltungsbox würden **zwei separate Einträge** erscheinen (Umzug + Reinigung)
- Firma sieht nicht direkt, dass es ein Kombi-Kauf war
- **Mögliche Lösung:** Spezielle Markierung in `offer_purchases` Tabelle mit `combo_purchase_id`?

#### Problem 3: Bewertungen
- Soll die Firma **2 separate Bewertungen** abgeben (eine für Umzug, eine für Reinigung)?
- Oder **eine gemeinsame** Bewertung für beide?
- Wenn gemeinsam: Wie wird diese gespeichert ohne echten Kombi-Eintrag?

#### Problem 4: Filter/Suche
- Wenn Firma nur "Umzug" filtert, sieht sie ihre gekaufte Kombi-Offerte nicht vollständig
- Kombi-Käufe müssten in **beiden Kategorien** auftauchen

### Bewertung:
Unklar - entweder 2 separate oder komplexes Bewertungssystem für virtuelle Kombis

### Verwaltungsbox:
Zwei separate Einträge mit Markierung "Teil einer Kombi"

---

## **Variante 5: Einzelofferten mit automatischem Kombi-Rabatt beim gleichzeitigen Kauf**

### Wie funktioniert es:
- Nur zwei Offerten existieren (Umzug + Reinigung)
- Wenn eine Firma **beide innerhalb von z.B. 24 Stunden** kauft, erhält sie automatisch eine **Rückerstattung** in Höhe des Kombi-Rabatts
- Oder: Beim zweiten Kauf erkennt das System den ersten und bietet Rabatt an

### Vorteile:
- ✅ Einfache Datenbankstruktur
- ✅ Flexibilität für Einzelkäufe
- ✅ Automatischer Anreiz für Kombi-Käufe

### Nachteile:
- ❌ Rabatt nicht sofort sichtbar beim ersten Kauf
- ❌ Komplexe Erkennungslogik
- ❌ Zeitlimit könnte Firmen unter Druck setzen

### Bewertung:
2 separate Bewertungen

### Verwaltungsbox:
2 Einträge mit Hinweis "Kombi-Rabatt erhalten"

---

## Meine Empfehlung

**Variante 3** (Einzelofferten + Kombi mit geteiltem Counter) bietet die beste Balance zwischen:
- Flexibilität für Firmen
- Klare Limitierung (max. 4 Verkäufe insgesamt)
- Überschaubare Komplexität
- Klare Darstellung in Verwaltungsbox und bei Bewertungen

**Alternative:** **Variante 1** ist die einfachste Lösung, wenn Einzelkäufe nicht zwingend notwendig sind.

---

## Nächste Schritte

Welche Variante bevorzugt ihr? Oder soll ich Details zu einer bestimmten Variante weiter ausarbeiten?
