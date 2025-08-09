<?php

return [
    'title' => 'Verifica riuscita',
    'successMessage' => '✅ Il tuo numero di telefono è stato verificato con successo!',
    'continue' => 'Continua',

    // Gemeinsame Keys
    'confirmTitle' => 'Inserisci codice',
    'enterCode' => 'Inserisci il codice di conferma',
    'smsSending' => 'Stiamo inviando un SMS con il tuo codice di conferma al <strong id="sms-number">{phone}</strong>.',
    'callSending' => 'Riceverai una chiamata a breve su <strong>{phone}</strong> con il tuo codice di conferma.',
    'codeLabel' => 'Codice di conferma',
    'submitCode' => 'Conferma codice',
    'changePhoneNote' => 'Se il numero mostrato non è corretto o non hai ricevuto il codice, inserisci il numero corretto qui sotto e invia nuovamente il modulo.',
    'note' => 'Nota',
    'noteText' => 'Richieste troppo frequenti vengono automaticamente bloccate.',
    'phoneLabel' => 'Numero di telefono',
    'changePhone' => 'Modifica numero di telefono',

    // SMS-Status-Meldungen
    'smsDelivered' => '✅ SMS consegnato con successo a {phone}.',
    'smsPending' => '⏳ SMS in consegna... Attendere prego.',
    'smsNoResult' => '⏳ Stato in fase di rilevamento... Attendere prego.',
    'smsInvalidNumber' => '❌ SMS non consegnato. Controlla il numero {phone}.',
    'smsError' => '❌ Errore nell\'invio SMS: {error}',
    'smsUnknown' => 'ℹ️ Stato: {status}. Attendere prego...',
    'smsConnectionError' => '⚠️ Errore di connessione nel recupero dello stato SMS. Riprova...',
    'smsFailed' => '❌ Impossibile rilevare lo stato, potrebbe indicare che il numero non è corretto. Se non hai ricevuto SMS, controlla il numero inserito e clicca su "Modifica numero di telefono". <a href="/verification/confirm">Clicca qui per richiedere un nuovo codice.</a>',

    // Allgemeine Meldungen
    'phoneMissing' => 'Numero di telefono mancante.',
    'phoneNotChanged' => 'Il numero di telefono non è stato modificato.',
    'phoneRequired' => 'Per favore inserisci un numero di telefono.',
    'errorSendingCode' => 'Errore nell\'invio del codice.',
    'fixedLineOnlyCall' => 'Numero fisso: verifica solo tramite chiamata.',
    'chooseMethod' => 'Seleziona il metodo di verifica.',
    'invalidRequest' => 'Richiesta non valida.',
    'wrongCode' => 'Codice errato. Riprova.',
    'invalidVerificationLink' => 'Link di verifica non valido.',
    'invalidOrOldVerificationLink' => 'Link di verifica non valido o scaduto.',
    'alreadyVerified' => 'Offerta già verificata.',
    'noOfferFound' => 'Nessuna richiesta trovata.',
    'smsVerificationCode' => 'Il tuo codice di verifica per {sitename} è: {code}',
    'callVerificationCode' => 'Il tuo codice di verifica per {sitename} è',

    // Optional für Weiterleitungen etc.
    'verificationSuccess' => '✅ Il tuo numero di telefono è stato verificato con successo!',
];
