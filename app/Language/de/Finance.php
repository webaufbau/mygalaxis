<?php

return [
    'title'           => 'Finanzen',
    'currentBalance'  => 'Aktuelles Guthaben',
    'amountCHF'       => 'Betrag (CHF)',
    'topupButton'     => 'Guthaben aufladen',
    'year'            => 'Jahr',
    'allYears'        => 'Alle Jahre',
    'month'           => 'Monat',
    'showButton'      => 'Anzeigen',
    'pdfExport'       => 'Buchungen PDF Export',
    'noBookings'      => 'Keine Buchungen gefunden.',
    'noBookingsForMonth' => 'Keine Buchungen für diesen Monat gefunden.',
    'date'            => 'Datum',
    'type'            => 'Typ',
    'description'     => 'Beschreibung',
    'amount'          => 'Betrag',
    'cardPayment'     => 'Kartenzahlung',
    'balanceChange'   => 'Guthaben-Änderung',
    'invoice'         => 'Rechnung',
    'paidByCard'      => 'Per Kreditkarte bezahlt',

    // Fehlermeldungen & Benachrichtigungen
    'messageInvalidAmount' => 'Ungültiger Betrag.',
    'messageCreditAdded' => 'Guthaben erfolgreich aufgeladen.',
    'messageTopupSuccess' => 'Guthaben erfolgreich aufgeladen.',
    'messageTransactionNotFound' => 'Transaktion nicht gefunden.',
    'messagePaymentSuccess' => 'Zahlung erfolgreich.',
    'messagePaymentMethodSaved' => 'Zahlungsmethode gespeichert.',
    'messagePaymentMethodDeleted' => 'Zahlungsmethode gelöscht.',
    'errorIncompleteAddress' => 'Ihre Adresse ist unvollständig oder ungültig. Bitte korrigieren Sie diese, um Ihr Guthaben aufzuladen.',
    'errorPaymentFailed' => 'Zahlung fehlgeschlagen',
    'errorPaymentNotAuthorized' => 'Zahlung nicht autorisiert.',
    'errorPaymentCheck' => 'Fehler bei der Zahlungsprüfung. Bitte versuchen Sie es später erneut oder kontaktieren Sie den Support.',
    'errorAmountExceeded' => 'Die Zahlung wurde abgelehnt, da das verfügbare Guthaben oder Kreditlimit nicht ausreicht.',
    'errorPaymentPageNotCreated' => 'Zahlungsseite konnte nicht erstellt werden.',
    'errorNoTokenReceived' => 'Kein Token erhalten.',
    'errorNotFoundOrDenied' => 'Nicht gefunden oder Zugriff verweigert.',

    // PDF
    'pdf_title'    => 'Buchungen',

    // Monatrechnung
    'monthlyInvoice' => 'Monatsrechnung',
    'invoiceDate' => 'Rechnungsdatum',
    'invoiceNumber' => 'Rechnungsnummer',
    'billingPeriod' => 'Abrechnungszeitraum',
    'bookingNr' => 'Booking-Nr.',
    'totalAmount' => 'Gesamtbetrag',
    'customer' => 'Kunde',
    'paymentNote' => 'Diese Rechnung wurde bereits vollständig im Voraus über Saferpay beglichen.',
    'thankYou' => 'Wir danken Ihnen für Ihren Einkauf und Ihr Vertrauen.',

    // MWST und Bankdaten
    'vatNumber' => 'UID-Nummer',
    'amountExclVat' => 'Betrag exkl. MWST',
    'vat' => 'MWST',
    'amountInclVat' => 'Betrag inkl. MWST',
    'serviceDate' => 'Leistungsdatum',
    'servicePeriod' => 'Leistungszeitraum',
    'bankDetails' => 'Bankverbindung',
    'iban' => 'IBAN',
    'bank' => 'Bank',
    'contact' => 'Kontakt',

    // Für topupFail View
    'topupFailTitle'   => 'Zahlung fehlgeschlagen',
    'topupFailMessage' => 'Ihre Aufladung konnte leider nicht abgeschlossen werden.',
    'backToTopup'      => 'Zurück zur Aufladung',

    // Sonstige
    'topupDescription' => 'Guthaben aufgeladen',
    'onlinePayment'    => 'Online-Zahlung',

    // Auflade-Seite
    'topupTitle' => 'Guthaben aufladen',
    'insufficientBalance' => 'Ihr Guthaben reicht für diesen Kauf nicht aus.',
    'requiredAmount' => 'Benötigter Betrag',
    'missingAmount' => 'Fehlender Betrag',
    'topupAmount' => 'Aufladebetrag (CHF)',
    'minimumTopupAmount' => 'Mindestaufladung: %s CHF',
    'topupNow' => 'Jetzt aufladen',
    'backToOffers' => 'Zurück zu Angeboten',
];
