<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

return [
    'registerTitle'           => 'Registrati',
    'emailAddress'            => 'Indirizzo e-mail',
    'emailRequired'           => 'Per favore inserisci un indirizzo e-mail valido.',
    'password'                => 'Password',
    'passwordMinLength'       => 'Inserisci una password di almeno 6 caratteri.',
    'passwordConfirm'         => 'Conferma password',
    'passwordConfirmRequired' => 'Per favore conferma la tua password.',
    'passwordsMismatch'       => 'Le password non corrispondono.',
    'companyDataTitle'        => 'Dati aziendali',
    'companyName'             => 'Nome dell\'azienda',
    'companyNameRequired'     => 'Per favore inserisci il nome dell\'azienda.',
    'companyUid'              => 'Registro commerciale (UID)',
    'companyUidRequired'      => 'Inserisci l\'UID nel formato %s.',
    'companyStreet'           => 'Via',
    'companyStreetRequired'   => 'Per favore inserisci la via.',
    'companyZip'              => 'CAP',
    'companyZipRequired'      => 'Per favore inserisci il codice postale.',
    'companyCity'             => 'Città',
    'companyCityRequired'     => 'Per favore inserisci la città.',
    'companyPhone'            => 'Numero di telefono',
    'companyPhoneRequired'    => 'Per favore inserisci un numero di telefono valido nel formato %s.',
    'companyWebsite'          => 'Sito web',
    'companyWebsiteRequired'  => 'Per favore inserisci un URL valido.',
    'selectAtLeastOneCategory'=> 'Please select at least one category.',
    'registerButton'          => 'Registrati',

    // Messages
    'loginFailed'             => 'Accesso fallito',
    'messageInvalidEmail'     => 'Per favore inserisci un indirizzo e-mail valido.',
    'resetLinkSentIfRegistered' => 'Se l\'e-mail è registrata, riceverai un link per reimpostare la password.',
    'resetPasswordSubject'    => 'Reimposta la password',
    'resetPasswordMessage'    => "Ciao,\n\nPer favore clicca sul seguente link per reimpostare la tua password:\n%s\n\nIl link è valido per 1 ora.",
    'enterEmail'              => 'Per favore inserisci il tuo indirizzo e-mail.',
    'userNotFound'            => 'Utente non trovato.',
    'invalidOrExpiredLink'    => 'Link non valido o scaduto.',
    'invalidRequest'          => 'Richiesta non valida.',
    'passwordsDontMatch'      => 'Le password non corrispondono.',
    'passwordChangedSuccess'  => 'Password modificata con successo. Ora puoi effettuare il login.',
    'emailAlreadyRegistered'  => 'L\'e-mail è già registrata.',
    'registrationSuccess'     => 'Registrazione riuscita. Ora puoi accedere.',

    // Exceptions
    'unknownAuthenticator'    => '{0} non è un autenticatore valido.',
    'unknownUserProvider'     => 'Non è stato possibile determinare il provider utente da utilizzare.',
    'invalidUser'             => 'L\'utente specificato non è stato trovato.',
    'bannedUser'              => 'Accesso negato, il tuo utente è attualmente bloccato.',
    'logOutBannedUser'        => 'Il tuo utente è stato disconnesso e bloccato.',
    'badAttempt'              => 'Impossibile effettuare il login. Verifica le tue credenziali.',
    'noPassword'              => 'Impossibile validare un utente senza password.',
    'invalidPassword'         => 'Impossibile effettuare il login. Controlla la tua password.',
    'noToken'                 => 'Ogni richiesta deve contenere un token bearer nell\'header {0}.',
    'badToken'                => 'Il token di accesso non è valido.',
    'oldToken'                => 'Il token di accesso è scaduto.',
    'noUserEntity'            => 'L\'entità utente deve essere fornita per la verifica della password.',
    'invalidEmail'            => 'Impossibile verificare se l\'indirizzo e-mail corrisponde a quello memorizzato.',
    'unableSendEmailToUser'   => 'Si è verificato un problema nell\'invio dell\'e-mail. Non siamo riusciti a inviare un\'e-mail a "{0}".',
    'throttled'               => 'Troppe richieste da questo indirizzo IP. Puoi riprovare tra {0} secondi.',
    'notEnoughPrivilege'      => 'Non hai i privilegi necessari per eseguire questa operazione.',

    // JWT Exceptions
    'invalidJWT'              => 'Il token non è valido.',
    'expiredJWT'              => 'Il token è scaduto.',
    'beforeValidJWT'          => 'Il token non è ancora valido.',

    'email'                   => 'Indirizzo e-mail',
    'username'                => 'Nome utente',
    //'password'              => 'Passwort',
    //'passwordConfirm'       => 'Passwort (erneut)',
    'haveAccount'             => 'Hai già un account?',
    'token'                   => 'Token',

    // Buttons
    'confirm' => 'Conferma',
    'send'    => 'Invia',

    // Registration
    'register'         => 'Registrati',
    'registerDisabled' => 'La registrazione non è consentita al momento.',
    'registerSuccess'  => 'Benvenuto a bordo!',

    // Login
    'login'               => 'Accedi',
    'needAccount'         => 'Hai bisogno di un account?',
    'rememberMe'          => 'Resta connesso',
    'forgotPassword'      => 'Password dimenticata?',
    'useMagicLink'        => 'Usa un link di accesso',
    'magicLinkSubject'    => 'Il tuo link di accesso',
    'magicTokenNotFound'  => 'Il link non può essere verificato.',
    'magicLinkExpired'    => 'Spiacenti, il link è scaduto.',
    'checkYourEmail'      => 'Controlla la tua e-mail!',
    'magicLinkDetails'    => 'Abbiamo appena inviato un\'e-mail con un link di accesso. È valido solo per {0} minuti.',
    'magicLinkDisabled'   => 'L\'uso di MagicLink non è consentito al momento.',
    'successLogout'       => 'Sei stato disconnesso con successo.',
    'backToLogin'         => 'Torna al login',

    // Passwords
    'errorPasswordLength'       => 'Le password devono essere lunghe almeno {0, number} caratteri.',
    'suggestPasswordLength'     => 'Le passphrase - fino a 255 caratteri - creano password più sicure e facili da ricordare.',
    'errorPasswordCommon'       => 'La password non può essere una password comune.',
    'suggestPasswordCommon'     => 'La password è stata confrontata con oltre 65 mila password comuni o compromesse in violazioni.',
    'errorPasswordPersonal'     => 'Le password non devono contenere informazioni personali hashate.',
    'suggestPasswordPersonal'   => 'Non usare variazioni del tuo indirizzo e-mail o nome utente come password.',
    'errorPasswordTooSimilar'   => 'La password è troppo simile al nome utente.',
    'suggestPasswordTooSimilar' => 'Non usare parti del nome utente nella password.',
    'errorPasswordPwned'        => 'La password {0} è stata esposta in una violazione della privacy ed è stata trovata {1, number} volte in {2} password compromesse.',
    'suggestPasswordPwned'      => '{0} non dovrebbe mai essere usata come password. Se la usi da qualche parte, cambiala subito.',
    'errorPasswordEmpty'        => 'È richiesta una password.',
    'errorPasswordTooLongBytes' => 'La password non può superare la lunghezza di {param} byte.',
    'passwordChangeSuccess'     => 'Password modificata con successo',
    'userDoesNotExist'          => 'Password non modificata. L\'utente non esiste',
    'resetTokenExpired'         => 'Spiacenti. Il tuo token di reset è scaduto.',

    // Email Globals
    'emailInfo'      => 'Alcune informazioni sulla persona:',
    'emailIpAddress' => 'Indirizzo IP:',
    'emailDevice'    => 'Dispositivo:',
    'emailDate'      => 'Data:',

    // 2FA
    'email2FATitle'        => 'Autenticazione a due fattori',
    'confirmEmailAddress'  => 'Conferma il tuo indirizzo e-mail.',
    'emailEnterCode'       => 'Conferma la tua e-mail',
    'emailConfirmCode'     => 'Inserisci il codice a 6 cifre che abbiamo appena inviato al tuo indirizzo e-mail.',
    'email2FASubject'      => 'Il tuo codice di autenticazione',
    'email2FAMailBody'     => 'Il tuo codice di autenticazione è:',
    'invalid2FAToken'      => 'Il codice non è corretto.',
    'need2FA'              => 'Devi completare la verifica a due fattori.',
    'needVerification'     => 'Controlla la tua e-mail per completare l\'attivazione dell\'account.',

    // Activate
    'emailActivateTitle'      => 'Attivazione e-mail',
    'emailActivateBody'       => 'Abbiamo appena inviato un\'e-mail con un codice per confermare il tuo indirizzo e-mail. Copia questo codice e incollalo qui sotto.',
    'emailActivateSubject'    => 'Il tuo codice di attivazione',
    'emailActivateMailBody'   => 'Usa il codice qui sotto per attivare il tuo account e utilizzare il sito web.',
    'invalidActivateToken'    => 'Il codice non è corretto.',
    'needActivate'            => 'Devi completare la registrazione confermando il codice inviato al tuo indirizzo e-mail.',
    'activationBlocked'       => 'Devi attivare il tuo account prima di poter accedere.',

    // Groups
    'unknownGroup'     => '{0} non è un gruppo valido.',
    'missingTitle'     => 'I gruppi devono avere un titolo.',

    // Permissions
    'unknownPermission' => '{0} non è un permesso valido.',

    // AGB
    'acceptAGBRequired' => 'Devi accettare i termini e condizioni.',


    'linkNotClickable' => 'If the link is not clickable in your email client, please copy and paste the following link into your browser:',

    'isUniqueEmail' => 'Gestiamo più portali. Il tuo indirizzo e-mail è già collegato a un account. Puoi accedere con la tua e-mail o richiedere l\'accesso diretto al tuo account esistente tramite il Magic Link.',

];
