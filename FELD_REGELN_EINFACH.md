# ✨ Feld-Darstellungs-Regeln - Einfache Version

## 🎯 Was ist das?

Ein **visuelles Tool** direkt im Email-Template-Editor, um bedingte Felder intelligent darzustellen.

**Ohne Programmierung!** ✅

---

## 🚀 Wie funktioniert es?

### **Schritt 1: Email-Template bearbeiten**

Gehe zu `/admin/email-templates/edit/4` (oder jedes andere Template)

### **Schritt 2: Aktiviere "Feld-Regeln"**

Scrolle zum neuen Abschnitt **"Feld-Darstellungs-Regeln"** und aktiviere den Schalter.

### **Schritt 3: Regel erstellen (visuell!)**

Klicke auf "Neue Feld-Regel hinzufügen" und fülle aus:

#### **Beispiel: Bodenplatten Vorplatz**

1. **Was soll angezeigt werden?**
   ```
   Bodenplatten: Vorplatz / Garage
   ```

2. **Welche Felder sollen versteckt werden?**
   ```
   bodenplatten_vorplatz
   bodenplatten_vorplatz_flaeche
   bodenplatten_vorplatz_flaeche_ja
   ```

3. **Bedingungen:**

   **Variante 1:**
   - WENN `bodenplatten_vorplatz` = `Ja`
   - UND `bodenplatten_vorplatz_flaeche` = `Ja`
   - DANN anzeigen: `{bodenplatten_vorplatz_flaeche_ja} m²`

   **Variante 2:**
   - WENN `bodenplatten_vorplatz` = `Ja`
   - UND `bodenplatten_vorplatz_flaeche` = `Nein`
   - DANN anzeigen: `Fläche unbekannt`

### **Schritt 4: Im Template verwenden**

Im Email-Template HTML einfach schreiben:
```html
<h3>Ihre Anfrage</h3>
<ul>
[show_all exclude="email,phone,terms"]
</ul>
```

**Fertig!** Die Regeln werden automatisch angewendet! 🎉

---

## 📊 Vorher vs. Nachher

### **Vorher** (manuell im Template):
```html
<ul>
[if field:bodenplatten_vorplatz == Ja]
    [if field:bodenplatten_vorplatz_flaeche == Ja]
        <li><strong>Vorplatz / Garage:</strong> {field:bodenplatten_vorplatz_flaeche_ja} m²</li>
    [/if]
    [if field:bodenplatten_vorplatz_flaeche == Nein]
        <li><strong>Vorplatz / Garage:</strong> Fläche unbekannt</li>
    [/if]
[/if]
</ul>
```

### **Nachher** (visuell im Editor):
```html
<ul>
[show_all]
</ul>
```

**Die Bedingungen werden visuell definiert - kein Code!**

---

## 🎨 Visuelles Interface

### **Aussehen:**

```
┌─────────────────────────────────────────────┐
│ ✓ Feld-Regeln für diese Branche definieren │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ ✓ Regel #1                            [🗑️]  │
├─────────────────────────────────────────────┤
│ Was soll angezeigt werden?                  │
│ ┌─────────────────────────────────────────┐ │
│ │ Bodenplatten: Vorplatz / Garage         │ │
│ └─────────────────────────────────────────┘ │
│                                             │
│ Welche Felder sollen versteckt werden?      │
│ ┌─────────────────────────────────────────┐ │
│ │ bodenplatten_vorplatz                   │ │
│ │ bodenplatten_vorplatz_flaeche           │ │
│ │ bodenplatten_vorplatz_flaeche_ja        │ │
│ └─────────────────────────────────────────┘ │
│                                             │
│ ─────────────────────────────────────────  │
│                                             │
│ Bedingungen:                                │
│                                             │
│ Variante 1:                          [❌]  │
│ ┌─────────────────────────────────────────┐ │
│ │ bodenplatten_vorplatz = Ja         [❌] │ │
│ │ bodenplatten_vorplatz_flaeche = Ja [❌] │ │
│ └─────────────────────────────────────────┘ │
│ [+ UND-Bedingung hinzufügen]                │
│                                             │
│ Dann anzeigen:                              │
│ ┌─────────────────────────────────────────┐ │
│ │ {bodenplatten_vorplatz_flaeche_ja} m²   │ │
│ └─────────────────────────────────────────┘ │
│                                             │
│ [+ Weitere Alternative hinzufügen (ODER)]   │
└─────────────────────────────────────────────┘

[+ Neue Feld-Regel hinzufügen]
```

---

## 💡 Features

### ✅ **Visuell & Einfach**
- Keine JSON
- Keine Programmierung
- Klicken & Ausfüllen

### ✅ **Flexibel**
- Mehrere Varianten (ODER-Verknüpfung)
- Mehrere Bedingungen (UND-Verknüpfung)
- Platzhalter für dynamische Werte

### ✅ **Wiederverwendbar**
- Regeln gelten in **Email** UND **Firmen-Ansicht**
- Pro Branche (offer_type) definierbar

### ✅ **Automatisch**
- Im Template nur `[show_all]` schreiben
- System wendet Regeln automatisch an

---

## 🔧 Setup

### **1. Migration ausführen**
```bash
ddev start
php spark migrate
```

### **2. Email-Template bearbeiten**
- Gehe zu `/admin/email-templates/edit/4`
- Scrolle zu "Feld-Darstellungs-Regeln"
- Aktiviere den Schalter
- Füge Regeln hinzu

### **3. Template vereinfachen**
```html
<!-- Alt (komplex): -->
[if field:bodenplatten_vorplatz == Ja]
  [if field:bodenplatten_vorplatz_flaeche == Ja]
    ...
  [/if]
[/if]

<!-- Neu (einfach): -->
[show_all]
```

---

## 📖 Platzhalter verwenden

Im Feld "Dann anzeigen" kannst du verwenden:

- `{feldname}` → Zeigt Wert des Feldes
- Beispiele:
  - `{bodenplatten_vorplatz_flaeche_ja} m²`
  - `{baum_entfernen_baumart} ({baum_entfernen_anzahl} Stück)`
  - `Fläche unbekannt` (statischer Text)

---

## 🎯 Beispiele

### **Beispiel 1: Einfache Ja/Nein-Anzeige**

**Regel:**
- Label: `Keller vorhanden`
- Versteckte Felder: `keller`
- Bedingung: `keller` = `Ja`
- Anzeige: `Ja`

**Ergebnis:**
```
Keller vorhanden: Ja
```

---

### **Beispiel 2: Mit Anzahl**

**Regel:**
- Label: `Bäume entfernen`
- Versteckte Felder: `baum_entfernen, baum_entfernen_baumart, baum_entfernen_anzahl`
- Bedingung: `baum_entfernen` = `Ja`
- Anzeige: `{baum_entfernen_baumart} ({baum_entfernen_anzahl} Stück)`

**Ergebnis:**
```
Bäume entfernen: Eiche (3 Stück)
```

---

### **Beispiel 3: Mehrere Varianten**

**Regel:**
- Label: `Rasen`
- Versteckte Felder: `rasen_maehen, rasen_maehen_flaeche`

**Variante 1:**
- Bedingung: `rasen_maehen` = `Ja`, `rasen_maehen_flaeche` = `Ja`
- Anzeige: `Mähen ({rasen_maehen_flaeche} m²)`

**Variante 2:**
- Bedingung: `rasen_maehen` = `Ja`, `rasen_maehen_flaeche` = `Nein`
- Anzeige: `Mähen (Fläche unbekannt)`

**Ergebnis:**
```
Rasen: Mähen (50 m²)
```

---

## ❓ FAQ

### **Muss ich JSON können?**
**Nein!** Das System generiert das JSON automatisch im Hintergrund.

### **Werden alte Templates weiterhin funktionieren?**
**Ja!** Bestehende Templates mit `[if...]` funktionieren weiterhin.

### **Kann ich Regeln später ändern?**
**Ja!** Einfach Template bearbeiten und Regeln anpassen.

### **Gelten Regeln auch für Firmen-Ansicht?**
**Ja!** Automatisch, keine Änderungen nötig.

---

## ✅ Vorteile

| | **Ohne Regeln** | **Mit Regeln** |
|---|---|---|
| **Email-Template** | 200+ Zeilen If-Bedingungen | 3 Zeilen `[show_all]` |
| **Änderungen** | In jedem Template einzeln | 1x im Email-Template-Editor |
| **Programmierkenntnisse** | Ja, Shortcodes verstehen | Nein, visueller Editor |
| **Firmen-Ansicht** | Separater Code | Automatisch gleich |
| **Wiederverwendung** | Nicht möglich | Überall gleich |

---

🎉 **Viel Erfolg!**
