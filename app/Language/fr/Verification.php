<?php

return [
    'title' => 'Vérification réussie',
    'successMessage' => '✅ Votre numéro de téléphone a été vérifié avec succès!',
    'continue' => 'Continuer',

    // Gemeinsame Keys
    'confirmTitle' => 'Saisir le code',
    'enterCode' => 'Entrez le code de confirmation',
    'smsSending' => 'Nous envoyons un SMS avec votre code de confirmation au <strong id="sms-number">{phone}</strong>.',
    'callSending' => 'Vous recevrez un appel sur <strong>{phone}</strong> dans quelques secondes avec votre code de confirmation.',
    'codeLabel' => 'Code de confirmation',
    'submitCode' => 'Confirmer le code',
    'changePhoneNote' => 'Si le numéro affiché n\'est pas correct ou si vous n\'avez pas reçu de code, veuillez entrer votre numéro correct ci-dessous et soumettre à nouveau le formulaire.',
    'note' => 'Note',
    'noteText' => 'Des demandes trop fréquentes seront automatiquement bloquées.',
    'phoneLabel' => 'Numéro de téléphone',
    'changePhone' => 'Modifier le numéro de téléphone',

    // SMS-Status-Meldungen
    'smsDelivered' => '✅ SMS livré avec succès à {phone}.',
    'smsPending' => '⏳ SMS en cours de livraison... Veuillez patienter.',
    'smsNoResult' => '⏳ Statut en cours de récupération... Veuillez patienter.',
    'smsInvalidNumber' => '❌ SMS non livré. Veuillez vérifier le numéro {phone}.',
    'smsError' => '❌ Erreur lors de l\'envoi du SMS: {error}',
    'smsUnknown' => 'ℹ️ Statut: {status}. Veuillez patienter...',
    'smsConnectionError' => '⚠️ Erreur de connexion lors de la récupération du statut SMS. Nouvelle tentative en cours...',
    'smsFailed' => '❌ Impossible de récupérer le statut, cela peut indiquer que le numéro est incorrect. Si vous n\'avez pas reçu de SMS, veuillez vérifier le numéro saisi et cliquer ensuite sur « Modifier le numéro de téléphone ». <a href="/verification/confirm">Cliquez ici pour demander un nouveau code.</a>',

    // Allgemeine Meldungen
    'phoneMissing' => 'Numéro de téléphone manquant.',
    'phoneNotChanged' => 'Le numéro de téléphone n\'a pas été modifié.',
    'phoneRequired' => 'Veuillez saisir un numéro de téléphone.',
    'errorSendingCode' => 'Erreur lors de l\'envoi du code.',
    'fixedLineOnlyCall' => 'Numéro fixe: seule la vérification par appel est possible.',
    'chooseMethod' => 'Veuillez choisir une méthode de vérification.',
    'invalidRequest' => 'Requête invalide.',
    'wrongCode' => 'Code incorrect. Veuillez réessayer.',
    'invalidVerificationLink' => 'Lien de vérification invalide.',
    'invalidOrOldVerificationLink' => 'Lien de vérification invalide ou expiré.',
    'alreadyVerified' => 'Offre déjà vérifiée.',
    'noOfferFound' => 'Aucune demande trouvée.',
    'smsVerificationCode' => 'Votre code de vérification pour {sitename} est: {code}',
    'callVerificationCode' => 'Votre code de vérification pour {sitename} est',

    // Optional für Weiterleitungen etc.
    'verificationSuccess' => '✅ Votre numéro de téléphone a été vérifié avec succès!',

];
