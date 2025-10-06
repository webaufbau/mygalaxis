<?php

return [
    'title'           => 'Finanze',
    'currentBalance'  => 'Saldo attuale',
    'amountCHF'       => 'Importo (CHF)',
    'topupButton'     => 'Ricarica saldo',
    'year'            => 'Anno',
    'allYears'        => 'Tutti gli anni',
    'month'           => 'Mese',
    'showButton'      => 'Mostra',
    'pdfExport'       => 'Esporta PDF',
    'noBookings'      => 'Nessuna prenotazione trovata.',
    'noBookingsForMonth' => 'Nessuna prenotazione trovata per questo mese.',
    'date'            => 'Data',
    'type'            => 'Tipo',
    'description'     => 'Descrizione',
    'amount'          => 'Importo',

    // Fehlermeldungen & Benachrichtigungen
    'messageInvalidAmount' => 'Importo non valido.',
    'messageCreditAdded' => 'Saldo ricaricato con successo.',
    'messageTransactionNotFound' => 'Transazione non trovata.',
    'messagePaymentSuccess' => 'Pagamento riuscito.',
    'messagePaymentMethodSaved' => 'Metodo di pagamento salvato.',
    'messagePaymentMethodDeleted' => 'Metodo di pagamento eliminato.',
    'errorIncompleteAddress' => 'Il tuo indirizzo è incompleto o non valido. Per favore correggilo per ricaricare il saldo.',
    'errorPaymentFailed' => 'Pagamento fallito',
    'errorPaymentNotAuthorized' => 'Pagamento non autorizzato.',
    'errorPaymentCheck' => 'Errore durante la verifica del pagamento. Riprova più tardi o contatta il supporto.',
    'errorAmountExceeded' => 'Il pagamento è stato rifiutato perché il saldo disponibile o il limite di credito non sono sufficienti.',
    'errorPaymentPageNotCreated' => 'Impossibile creare la pagina di pagamento.',
    'errorNoTokenReceived' => 'Nessun token ricevuto.',
    'errorNotFoundOrDenied' => 'Non trovato o accesso negato.',

    // PDF
    'pdf_title'    => 'Prenotazioni',

    // Monatrechnung
    'monthlyInvoice' => 'Fattura mensile',
    'invoiceDate' => 'Data fattura',
    'invoiceNumber' => 'Numero fattura',
    'billingPeriod' => 'Periodo di fatturazione',
    'bookingNr' => 'N. prenotazione',
    'totalAmount' => 'Importo totale',
    'customer' => 'Cliente',
    'invoice' => 'Fattura',
    'paymentNote' => 'Questa fattura è già stata completamente pagata in anticipo tramite Saferpay.',
    'thankYou' => 'La ringraziamo per il suo acquisto e la sua fiducia.',

    // IVA e coordinate bancarie
    'vatNumber' => 'Partita IVA',
    'amountExclVat' => 'Importo esclusa IVA',
    'vat' => 'IVA',
    'amountInclVat' => 'Importo inclusa IVA',
    'serviceDate' => 'Data della prestazione',
    'servicePeriod' => 'Periodo della prestazione',
    'bankDetails' => 'Coordinate bancarie',
    'iban' => 'IBAN',
    'bank' => 'Banca',
    'contact' => 'Contatto',

    // Für topupFail View
    'topupFailTitle'   => 'Pagamento fallito',
    'topupFailMessage' => 'La tua ricarica non è stata completata.',
    'backToTopup'      => 'Torna alla ricarica',

    // Sonstige
    'topupDescription' => 'Ricarica saldo via',
    'onlinePayment'    => 'Pagamento online',

    // Pagina di ricarica
    'topupTitle' => 'Ricarica saldo',
    'insufficientBalance' => 'Il tuo saldo non è sufficiente per questo acquisto.',
    'requiredAmount' => 'Importo richiesto',
    'missingAmount' => 'Importo mancante',
    'topupAmount' => 'Importo di ricarica (CHF)',
    'minimumTopupAmount' => 'Ricarica minima: %s CHF',
    'topupNow' => 'Ricarica ora',
    'backToOffers' => 'Torna alle offerte',
];
