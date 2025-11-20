# Möglichkeiten für Multi-Card System (Zeile 17 + 22)

## Aktueller Stand

**Datenbank:**
- Tabelle `user_payment_methods` ist bereits vorhanden
- Struktur unterstützt bereits MEHRERE Karten pro User
- Felder: `id`, `user_id`, `payment_method_code`, `platform`, `provider_data`, `created_at`, `updated_at`

**Payment Provider:**
- Saferpay wird verwendet
- Alias-System: Karten werden mit `RegisterAlias` gespeichert
- Verschlüsselte Token-Speicherung in `provider_data`

**Aktuelles Problem:**
- UI zeigt nur die ERSTE Karte: `$savedCard = $userPaymentMethods[0];` (finance.php:91)
- Button "Karte ändern" überschreibt die vorhandene Karte statt eine neue hinzuzufügen
- Keine Auswahl zwischen mehreren Karten möglich

---

## OPTION 1: Einfache Lösung (Primary + Secondary Card)

### Beschreibung:
Erweiterung um eine zweite Karte mit Primary/Secondary Status

### Änderungen:

#### 1. Datenbank erweitern:
```sql
ALTER TABLE user_payment_methods
ADD COLUMN is_primary TINYINT(1) DEFAULT 0 AFTER payment_method_code,
ADD COLUMN card_last4 VARCHAR(4) DEFAULT NULL AFTER is_primary,
ADD COLUMN card_brand VARCHAR(50) DEFAULT NULL AFTER card_last4,
ADD COLUMN card_expiry VARCHAR(7) DEFAULT NULL AFTER card_brand;
```

#### 2. UI auf Finance-Seite:
- Liste mit allen hinterlegten Karten (max. 2)
- Jede Karte zeigt: Brand (Visa/Mastercard/TWINT), Letzte 4 Ziffern, Ablaufdatum
- Radio-Button für Primary/Secondary
- "Weitere Karte hinzufügen" Button (nur wenn weniger als 2 Karten)
- "Entfernen" Button pro Karte

#### 3. Logik bei Zahlungen:
- Auto-Purchase: Verwendet Primary Card
- Manuelle Käufe: User kann bei Checkout wählen
- Fallback: Wenn Primary fehlschlägt, Secondary verwenden

### Vorteile:
- ✅ Einfache Implementierung
- ✅ Klare UX (2 Karten übersichtlich)
- ✅ Datenbank unterstützt es bereits
- ✅ Saferpay Alias-System funktioniert problemlos

### Nachteile:
- ⚠️ Limitierung auf 2 Karten
- ⚠️ Zusätzliche Migration nötig

### Aufwand: **MITTEL** (3-4 Stunden)

---

## OPTION 2: Unbegrenzte Karten mit Dropdown

### Beschreibung:
Beliebig viele Karten mit einer als "Standard" markiert

### Änderungen:

#### 1. Datenbank erweitern:
```sql
ALTER TABLE user_payment_methods
ADD COLUMN is_default TINYINT(1) DEFAULT 0,
ADD COLUMN card_last4 VARCHAR(4) DEFAULT NULL,
ADD COLUMN card_brand VARCHAR(50) DEFAULT NULL,
ADD COLUMN card_expiry VARCHAR(7) DEFAULT NULL,
ADD COLUMN nickname VARCHAR(100) DEFAULT NULL COMMENT 'User-defined nickname for card';
```

#### 2. UI auf Finance-Seite:
- Dropdown/Select mit allen Karten
- Jeder Eintrag: "Brand •••• 1234 (Nickname)"
- Stern/Favorit-Icon für Default-Karte
- "Neue Karte hinzufügen" Button
- Verwaltungs-Modal: Liste aller Karten mit Edit/Delete/Set as Default

#### 3. Logik:
- Default-Karte für Auto-Purchase
- Bei Checkout: Dropdown zur Auswahl
- Nickname-Funktion: "Geschäftskarte", "Privatkarte" etc.

### Vorteile:
- ✅ Flexibel (unbegrenzt viele Karten)
- ✅ Nickname macht Verwaltung einfacher
- ✅ Professional Appearance

### Nachteile:
- ⚠️ Komplexere UI
- ⚠️ Mehr Entwicklungsaufwand
- ⚠️ Evtl. verwirrend wenn zu viele Karten

### Aufwand: **HOCH** (6-8 Stunden)

---

## OPTION 3: Minimale Erweiterung (nur 2. Karte anzeigen)

### Beschreibung:
Schnellste Lösung - einfach beide Karten anzeigen ohne viel Logik

### Änderungen:

#### 1. Datenbank: KEINE Änderungen nötig

#### 2. UI auf Finance-Seite (finance.php):
```php
<?php if (count($userPaymentMethods) > 0): ?>
    <h5>Hinterlegte Karten:</h5>
    <?php foreach ($userPaymentMethods as $index => $card): ?>
        <div class="alert alert-success mb-2">
            <i class="bi bi-check-circle me-2"></i>
            Karte <?= $index + 1 ?>: <?= $cardBrand ?>
            <a href="<?= site_url('finance/remove-payment-method/' . $card['id']) ?>"
               class="btn btn-sm btn-outline-danger float-end">
                <i class="bi bi-trash"></i> Entfernen
            </a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (count($userPaymentMethods) < 2): ?>
    <a href="<?= site_url('finance/register-payment-method') ?>" class="btn btn-primary">
        <i class="bi bi-plus"></i> Weitere Karte hinzufügen
    </a>
<?php endif; ?>
```

#### 3. Controller:
- Keine Änderung bei Registrierung (fügt automatisch hinzu)
- Bei Zahlung: Verwendet erste Karte oder ermöglicht Auswahl

### Vorteile:
- ✅ SEHR schnell umsetzbar (30 Min - 1 Std)
- ✅ Keine DB-Migration
- ✅ Funktioniert sofort

### Nachteile:
- ⚠️ Kein Primary/Default Status
- ⚠️ Keine Kartendetails sichtbar
- ⚠️ Einfach aber nicht sehr elegant

### Aufwand: **NIEDRIG** (0.5-1 Stunden)

---

## OPTION 4: Material Design mit Card-Management Modal

### Beschreibung:
Moderne Lösung mit dediziertem Karten-Verwaltungs-Modal

### Änderungen:

#### 1. Datenbank wie Option 2

#### 2. UI Design:
- Hauptseite: Kompakte Liste der Karten
- "Karten verwalten" Button öffnet Modal
- Im Modal:
  - Grid-Layout mit Karten-Kacheln (wie echte Kreditkarten)
  - Jede Kachel zeigt Brand-Logo, Last4, Expiry
  - Actions: Set Default, Edit Nickname, Delete
  - "Neue Karte hinzufügen" als separate Kachel

#### 3. Features:
- Drag & Drop für Sortierung
- Visual Feedback (Default-Karte mit goldenem Rahmen)
- Confirmation Dialoge beim Löschen

### Vorteile:
- ✅ Sehr professionelles Aussehen
- ✅ Beste User Experience
- ✅ Macht Spaß zu benutzen

### Nachteile:
- ⚠️ Hoher Entwicklungsaufwand
- ⚠️ Benötigt JavaScript/AJAX
- ⚠️ Möglicherweise "over-engineered"

### Aufwand: **SEHR HOCH** (10-12 Stunden)

---

## EMPFEHLUNG

### Für sofortige Umsetzung: **OPTION 3** (Minimale Erweiterung)
**Warum:**
- Schnellste Lösung (96.8% → 100% in unter 1 Stunde)
- Erfüllt die Grundanforderung "2. Karte hinterlegen"
- Kann später zu Option 1 oder 2 ausgebaut werden
- Keine Breaking Changes

### Für beste Balance: **OPTION 1** (Primary + Secondary)
**Warum:**
- Guter Kompromiss zwischen Aufwand und Features
- 2 Karten reichen für 99% der User
- Professionelle Umsetzung
- Klare Primary/Secondary Logik für Auto-Purchase

### Für maximale Flexibilität: **OPTION 2** (Unbegrenzte Karten)
**Warum:**
- Zukunftssicher
- Nickname-Feature sehr nützlich
- Wirkt professionell
- Skalierbar

---

## Implementierungs-Schritte für OPTION 1 (Empfohlen)

### 1. Migration erstellen (5 Min)
```bash
php spark make:migration ExtendUserPaymentMethodsForMultiCard
```

### 2. Migration-Code (10 Min)
Siehe oben - Felder hinzufügen

### 3. Model erweitern (15 Min)
- `UserPaymentMethodModel.php` um Methoden für Primary/Secondary erweitern
- `getPrimaryCard()`, `getSecondaryCard()`, `setPrimary()`

### 4. Saferpay Service erweitern (30 Min)
- Bei Alias-Registrierung auch Kartendetails speichern
- Assert Response enthält: `PaymentMeans.Brand`, `PaymentMeans.DisplayText`

### 5. Finance Controller anpassen (45 Min)
- Mehrere Karten laden
- Remove-Funktion hinzufügen
- Primary Toggle-Logik

### 6. Finance View redesignen (90 Min)
- Karten-Liste statt einzelne Karte
- Primary/Secondary Radio Buttons
- Add/Remove Buttons
- Responsive Design

### 7. Auto-Purchase anpassen (30 Min)
- `OfferAutoBuy.php` verwendet Primary Card

### 8. Testing (60 Min)
- Karte hinzufügen
- Zweite Karte hinzufügen
- Primary wechseln
- Karte entfernen
- Auto-Purchase testen
- Manueller Kauf testen

**Total: ca. 4 Stunden**

---

## Was möchtest du umsetzen?

1. **Option 3** - Schnell und funktional (1 Stunde)
2. **Option 1** - Empfohlen, professionell (4 Stunden)
3. **Option 2** - Maximal flexibel (8 Stunden)
4. **Option 4** - Premium UX (12 Stunden)
