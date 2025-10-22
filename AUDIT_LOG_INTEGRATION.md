# Audit Log System - Integration Guide

## System Status
✅ **Datenbank**: Migration erstellt und ausgeführt
✅ **Model**: `FormAuditLogModel` erstellt
✅ **Helper**: `audit_helper.php` - einfache `auditLog()` Funktion
✅ **Test**: Command `test:audit-log` funktioniert

## Verwendung

```php
helper('audit');

auditLog(
    'event_type',           // z.B. 'form_submit', 'verification_sms_sent'
    'Beschreibung',         // Lesbare Nachricht
    [                       // Context
        'uuid' => $uuid,
        'offer_id' => $offerId,
        'email' => $email,
        'phone' => $phone,
        'platform' => $platform,
        'group_id' => $groupId,
    ],
    [                       // Details (optional, JSON)
        'any' => 'data',
        'you' => 'want',
    ]
);
```

## Zu implementierende Audit-Punkte

### 1. FluentForm.php - handle() Methode

**Bereits hinzugefügt:**
- ✅ Zeile 41-55: `form_handle_called` - Formular Handle aufgerufen

**Noch hinzuzufügen:**

```php
// Nach Zeile 220 (bei additional_service == 'Nein'):
auditLog(
    'redirect_to_verification',
    "Weiterleitung zur Verifikation (keine weitere Dienstleistung gewünscht)",
    ['uuid' => $uuid, 'phone' => session()->get('phone')],
    ['redirect_url' => 'processing?uuid=' . urlencode($uuid)]
);

// Nach Zeile 295 (bei Weiterleitung zu nächstem Formular):
auditLog(
    'redirect_to_next_form',
    "Weiterleitung zu nächstem Formular: {$next_url}",
    ['uuid' => $uuid, 'group_id' => session()->get('group_id')],
    ['redirect_url' => $redirectUrl, 'get_params' => $getParams]
);
```

### 2. FluentForm.php - webhook() Methode

```php
// Am Anfang der Methode (nach Zeile 328):
helper('audit');
auditLog(
    'form_webhook_received',
    "Webhook POST empfangen von WordPress - Typ: " . ($data['type'] ?? 'unknown'),
    [
        'uuid' => $data['uuid'] ?? null,
        'email' => $data['email'] ?? null,
        'phone' => $data['phone'] ?? null,
    ],
    ['post_data_keys' => array_keys($data)]
);

// Nach Zeile 677 (Offer wurde erstellt):
auditLog(
    'offer_created',
    "Offerte #{$offerId} erstellt - Typ: {$type}, Preis: {$calculatedPrice} CHF",
    [
        'uuid' => $uuid,
        'offer_id' => $offerId,
        'group_id' => $groupId,
        'platform' => $platform,
        'email' => $formFields['email'] ?? null,
        'phone' => $formFields['phone'] ?? null,
    ],
    [
        'type' => $type,
        'price' => $calculatedPrice,
        'verified' => $verified,
        'verify_type' => $verifyType,
    ]
);
```

### 3. Verification.php - processing() Methode

```php
// Am Anfang (nach Zeile 131):
helper('audit');
auditLog(
    'verification_processing_page',
    "Processing-Seite aufgerufen - warte auf Datensatz",
    ['uuid' => $uuid]
);
```

### 4. Verification.php - index() Methode

```php
// Nach Zeile 72 (Methode bestimmt):
auditLog(
    'verification_method_determined',
    "Verifikations-Methode bestimmt: {$method} für Telefonnummer {$phone}",
    [
        'uuid' => $uuid,
        'phone' => $phone,
    ],
    [
        'method' => $method,
        'is_mobile' => $isMobile,
    ]
);
```

### 5. Verification.php - send() Methode

```php
// Nach SMS-Versand (Zeile 232):
auditLog(
    'verification_sms_sent',
    "SMS-Verifikationscode {$verificationCode} gesendet an {$phone}",
    ['uuid' => session()->get('uuid'), 'phone' => $phone],
    ['code' => $verificationCode, 'provider' => 'infobip', 'status' => $infobipResponseArray['status']]
);

// Nach Anruf (Zeile 250):
auditLog(
    'verification_call_sent',
    "Anruf-Verifikationscode {$verificationCode} gesendet an {$phone}",
    ['uuid' => session()->get('uuid'), 'phone' => $phone],
    ['code' => $verificationCode, 'provider' => 'twilio']
);
```

### 6. Verification.php - verify() Methode

```php
// Bei Telefonnummer-Änderung (nach Zeile 346):
auditLog(
    'verification_phone_changed',
    "Telefonnummer geändert von {$oldPhone} zu {$normalizedPhone}",
    ['uuid' => $uuid, 'phone' => $normalizedPhone],
    ['old_phone' => session()->get('phone'), 'new_phone' => $normalizedPhone, 'method' => $method]
);

// Bei erfolgreicher Verifikation (nach Zeile 401):
auditLog(
    'verification_confirmed',
    "Verifikation erfolgreich bestätigt - Code korrekt eingegeben",
    [
        'uuid' => $uuid,
        'phone' => session()->get('phone'),
    ],
    ['verify_method' => session()->get('verify_method')]
);

// Bei falschem Code (nach Zeile 447):
auditLog(
    'verification_failed',
    "Verifikation fehlgeschlagen - falscher Code eingegeben",
    ['uuid' => session()->get('uuid')],
    ['entered_code' => $enteredCode, 'expected_code' => $sessionCode]
);
```

### 7. Verification.php - sendOfferNotificationEmail()

```php
// Nach erfolgreichem Versand (Zeile 823-831):
auditLog(
    'email_confirmation_sent',
    "Bestätigungsmail gesendet an {$userEmail} für Angebot #{$offer['id']}",
    [
        'uuid' => $uuid,
        'offer_id' => $offer['id'],
        'email' => $userEmail,
        'platform' => $platform,
    ],
    ['subject' => lang('Email.offer_added_email_subject')]
);
```

### 8. OfferNotificationSender.php - notifyMatchingUsers()

```php
// Nach Zeile 51 (nach dem Senden):
auditLog(
    'email_companies_notified',
    "Firmen benachrichtigt für Offerte #{$offer['id']}: {$sentCount} E-Mails versendet",
    [
        'offer_id' => $offer['id'],
        'platform' => $offer['platform'] ?? null,
    ],
    ['sent_count' => $sentCount, 'total_users_checked' => count($users)]
);
```

### 9. OfferNotificationSender.php - sendOfferEmail()

```php
// Nach dem Versand (nach if ($email->send())):
auditLog(
    'email_company_notification',
    "Neue Offerte #{$offer['id']} an Firma {$user->company_name} gesendet",
    [
        'offer_id' => $offer['id'],
        'email' => $to,
        'platform' => $siteConfig->name ?? null,
    ],
    ['company_id' => $user->id, 'company_name' => $user->company_name]
);
```

## Admin View erstellen

Ein einfacher Admin-Controller zum Anzeigen der Logs:

```php
// app/Controllers/Admin/AuditLog.php
public function index()
{
    $auditModel = new \App\Models\FormAuditLogModel();

    $filters = [
        'search' => $this->request->getGet('search'),
        'event_category' => $this->request->getGet('category'),
        'platform' => $this->request->getGet('platform'),
        'limit' => 100,
    ];

    $logs = $auditModel->getLogsFiltered($filters);

    return view('admin/audit_log/index', [
        'logs' => $logs,
        'filters' => $filters,
    ]);
}

public function byUuid(string $uuid)
{
    $auditModel = new \App\Models\FormAuditLogModel();
    $logs = $auditModel->getLogsByUuid($uuid);

    return view('admin/audit_log/by_uuid', [
        'uuid' => $uuid,
        'logs' => $logs,
    ]);
}
```

## Nützliche Queries

```sql
-- Alle Logs für eine UUID
SELECT * FROM form_audit_log WHERE uuid = 'xxx' ORDER BY created_at;

-- Alle Logs für eine Group
SELECT * FROM form_audit_log WHERE group_id = 'xxx' ORDER BY created_at;

-- Fehlgeschlagene Verifikationen
SELECT * FROM form_audit_log WHERE event_type = 'verification_failed';

-- Letzte 100 Events
SELECT * FROM form_audit_log ORDER BY created_at DESC LIMIT 100;
```

## Event Types Übersicht

### Form Events
- `form_handle_called` - Handle-Methode aufgerufen
- `form_webhook_received` - Webhook POST empfangen
- `offer_created` - Offerte in DB erstellt

### Redirect Events
- `redirect_to_verification` - Zu Verifikation weitergeleitet
- `redirect_to_next_form` - Zu nächstem Formular weitergeleitet

### Verification Events
- `verification_processing_page` - Processing-Seite aufgerufen
- `verification_method_determined` - SMS/Anruf bestimmt
- `verification_sms_sent` - SMS versendet
- `verification_call_sent` - Anruf gestartet
- `verification_phone_changed` - Telefonnummer geändert
- `verification_confirmed` - Erfolgreich bestätigt
- `verification_failed` - Fehlgeschlagen (falscher Code)

### Email Events
- `email_confirmation_sent` - Bestätigungsmail an Kunde
- `email_companies_notified` - Firmen benachrichtigt
- `email_company_notification` - Einzelne Firma benachrichtigt
