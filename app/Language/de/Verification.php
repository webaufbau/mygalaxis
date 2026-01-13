<?php

return [
    'title' => 'Verifizierung erfolgreich',
    'successMessage' => '✅ Ihre Telefonnummer wurde erfolgreich verifiziert!',
    'continue' => 'Weiter',

    // Gemeinsame Keys
    'confirmTitle' => 'Code eingeben',
    'enterCode' => 'Bestätigungscode eingeben',
    'smsSending' => 'Wir senden eine SMS mit Ihrem Bestätigungscode an <strong id="sms-number">{phone}</strong>.',
    'callSending' => 'Sie erhalten in wenigen Sekunden einen Anruf auf <strong>{phone}</strong> mit Ihrem Bestätigungscode.',
    'codeLabel' => 'Bestätigungscode',
    'submitCode' => 'Code bestätigen',
    'changePhoneNote' => 'Sollte die angezeigte Telefonnummer nicht korrekt sein oder Sie keinen Code erhalten haben, geben Sie bitte Ihre richtige Telefonnummer unten ein und senden Sie das Formular erneut ab.',
    'note' => 'Hinweis',
    'noteText' => 'Zu häufige Anfragen hintereinander werden automatisch blockiert.',
    'phoneLabel' => 'Telefonnummer',
    'changePhone' => 'Telefonnummer anpassen',

    // SMS-Status-Meldungen
    'smsDelivered' => '✅ SMS erfolgreich zugestellt an {phone}.',
    'smsPending' => '⏳ SMS wird zugestellt... Bitte warten.',
    'smsNoResult' => '⏳ Status wird ermittelt... Bitte warten.',
    'smsInvalidNumber' => '❌ SMS konnte nicht zugestellt werden. Bitte prüfen Sie die Nummer {phone}.',
    'smsError' => '❌ Fehler beim SMS-Versand: {error}',
    'smsUnknown' => 'ℹ️ Status: {status}. Bitte warten...',
    'smsConnectionError' => '⚠️ Verbindungsfehler beim Abrufen des SMS-Status. Versuche es erneut...',
    'smsFailed' => '❌ Der Status konnte leider nicht ermittelt werden, dies ist ein Hinweis, dass die Telefonnummer nicht korrekt sein könnte. Falls Sie keine SMS erhalten haben, überprüfen Sie bitte die eingegebene Telefonnummer und klicken Sie anschliessend auf „Telefonnummer anpassen“. <a href="/verification/confirm">Um einen neuen Code anzufordern klicken Sie hier.</a>',

    // Allgemeine Meldungen
    'phoneMissing' => 'Telefonnummer fehlt.',
    'phoneNotChanged' => 'Die Telefonnummer wurde nicht geändert.',
    'phoneRequired' => 'Bitte geben Sie eine Telefonnummer ein.',
    'errorSendingCode' => 'Fehler beim Versenden des Codes.',
    'fixedLineOnlyCall' => 'Festnetznummer: nur Anruf-Verifizierung möglich.',
    'chooseMethod' => 'Bitte Verifizierungsmethode wählen.',
    'invalidRequest' => 'Ungültige Anfrage.',
    'wrongCode' => 'Falscher Code. Bitte erneut versuchen.',
    'invalidVerificationLink' => 'Ungültiger Verifizierungslink.',
    'invalidOrOldVerificationLink' => 'Ungültiger oder abgelaufener Verifizierungslink.',
    'alreadyVerified' => 'Angebot bereits verifiziert.',
    'noOfferFound' => 'Keine Anfrage gefunden.',
    'smsVerificationCode' => 'Ihr Verifizierungscode für {sitename} lautet: {code}',
    'callVerificationCode' => 'Ihr Verifizierungscode für {sitename} lautet',

    // Optional für Weiterleitungen etc.
    'verificationSuccess' => '✅ Ihre Telefonnummer wurde erfolgreich verifiziert!',

    // Resend-Meldungen
    'newCodeSentSms' => 'Ein neuer Code wurde per SMS gesendet.',
    'newCodeSentCall' => 'Ein neuer Anruf wurde gestartet.',

];
