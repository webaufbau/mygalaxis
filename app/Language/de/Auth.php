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
    'registerTitle'           => 'Registrieren',
    'emailAddress'            => 'E-Mail-Adresse',
    'emailRequired'           => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
    'password'                => 'Passwort',
    'passwordMinLength'       => 'Bitte geben Sie ein Passwort mit mindestens 6 Zeichen ein.',
    'passwordConfirm'         => 'Passwort bestätigen',
    'passwordConfirmRequired' => 'Bitte bestätigen Sie Ihr Passwort.',
    'passwordsMismatch'       => 'Die Passwörter stimmen nicht überein.',
    'companyDataTitle'        => 'Firmendaten',
    'companyName'             => 'Firmenname',
    'companyNameRequired'     => 'Bitte geben Sie den Firmennamen ein.',
    'companyUid'              => 'Handelsregister (UID)',
    'companyUidRequired'      => 'Bitte geben Sie die UID im Format %s ein.',
    'companyStreet'           => 'Strasse',
    'companyStreetRequired'   => 'Bitte geben Sie die Strasse ein.',
    'companyZip'              => 'PLZ',
    'companyZipRequired'      => 'Bitte geben Sie die Postleitzahl ein.',
    'companyCity'             => 'Ort',
    'companyCityRequired'     => 'Bitte geben Sie den Ort ein.',
    'companyPhone'            => 'Telefonnummer',
    'companyPhoneRequired'    => 'Bitte geben Sie eine gültige Telefonnummer im Format %s ein.',
    'companyWebsite'          => 'Website',
    'companyWebsiteRequired'  => 'Bitte geben Sie eine gültige URL ein.',
    'contactPerson'           => 'Kontaktperson',
    'contactPersonRequired'   => 'Bitte geben Sie die Kontaktperson ein.',
    'selectAtLeastOneCategory'=> 'Bitte wählen Sie mindestens eine Branche aus.',
    'registerButton'          => 'Registrieren',

    // Messages
    'loginFailed' => 'Login fehlgeschlagen',
    'messageInvalidEmail' => 'Bitte gib eine gültige E-Mail-Adresse ein.',
    'resetLinkSentIfRegistered' => 'Falls die E-Mail registriert ist, erhältst du einen Link zum Zurücksetzen.',
    'resetPasswordSubject' => 'Passwort zurücksetzen',
    'resetPasswordMessage' => "Hallo,\n\nBitte klicke auf folgenden Link, um dein Passwort zurückzusetzen:\n%s\n\nDer Link ist 1 Stunde gültig.",
    'enterEmail' => 'Bitte gib deine E-Mail-Adresse ein.',
    'userNotFound' => 'Benutzer wurde nicht gefunden.',
    'invalidOrExpiredLink' => 'Ungültiger oder abgelaufener Link.',
    'invalidRequest' => 'Ungültige Anfrage.',
    'passwordsDontMatch' => 'Passwörter stimmen nicht überein.',
    'passwordChangedSuccess' => 'Passwort wurde erfolgreich geändert. Du kannst dich jetzt anmelden.',
    'emailAlreadyRegistered' => 'E-Mail ist bereits registriert.',
    'registrationSuccess' => 'Registrierung erfolgreich. Du kannst dich jetzt einloggen.',
    'adminOnly' => 'Dieser Login ist nur für Administratoren. Bitte verwenden Sie den regulären Login für Firmen.',

    // Exceptions
    'unknownAuthenticator'  => '{0} ist kein gültiger Authentifikator.',
    'unknownUserProvider'   => 'Der zu verwendende User Provider konnte nicht ermittelt werden.',
    'invalidUser'           => 'Der angegebene Benutzer kann nicht gefunden werden.',
    'bannedUser'            => 'Anmelden nicht möglich da Ihr Benutzer derzeit gesperrt ist.',
    'logOutBannedUser'      => 'Ihr Benutzer wurde abgemeldet und gesperrt.',
    'badAttempt'            => 'Sie konnten nicht angemeldet werden. Bitte überprüfen Sie Ihre Anmeldedaten.',
    'noPassword'            => 'Kann einen Benutzer ohne Passwort nicht validieren.',
    'invalidPassword'       => 'Sie können nicht angemeldet werden. Bitte überprüfen Sie Ihr Passwort.',
    'noToken'               => 'Jede Anfrage muss ein Überbringer-Token im {0}-Header enthalten.',
    'badToken'              => 'Das Zugriffstoken ist ungültig.',
    'oldToken'              => 'Das Zugriffstoken ist abgelaufen.',
    'noUserEntity'          => 'Die Benutzerentität muss für die Passwortüberprüfung angegeben werden.',
    'invalidEmail'          => 'Es konnte nicht überprüft werden, ob die E-Mail-Adresse mit der gespeicherten übereinstimmt.',
    'unableSendEmailToUser' => 'Leider gab es ein Problem beim Senden der E-Mail. Wir konnten keine E-Mail an "{0}" senden.',
    'throttled'             => 'Es wurden zu viele Anfragen von dieser IP-Adresse gestellt. Sie können es in {0} Sekunden erneut versuchen.',
    'notEnoughPrivilege'    => 'Sie haben nicht die erforderliche Berechtigung, um den gewünschten Vorgang auszuführen.',

    // JWT Exceptions
    'invalidJWT'     => 'Der Token ist ungültig.',
    'expiredJWT'     => 'Der Token ist abgelaufen.',
    'beforeValidJWT' => 'Der Token ist noch nicht verfügbar.',

    'email'           => 'E-Mail-Adresse',
    'username'        => 'Benutzername',
    //'password'        => 'Passwort',
    //'passwordConfirm' => 'Passwort (erneut)',
    'haveAccount'     => 'Haben Sie bereits ein Konto?',
    'token'           => 'Token',

    // Buttons
    'confirm' => 'Bestätigen',
    'send'    => 'Senden',

    // Registration
    'register'         => 'Registrieren',
    'registerDisabled' => 'Die Registrierung ist derzeit nicht erlaubt.',
    'registerSuccess'  => 'Willkommen an Bord!',

    // Login
    'login'              => 'Anmelden',
    'needAccount'        => 'Brauchen Sie ein Konto?',
    'rememberMe'         => 'Angemeldet bleiben',
    'forgotPassword'     => 'Passwort vergessen?',
    'useMagicLink'       => 'Einen Login-Link verwenden',
    'magicLinkSubject'   => 'Ihr Login-Link',
    'magicTokenNotFound' => 'Der Link konnte nicht verifiziert werden.',
    'magicLinkExpired'   => 'Sorry, der Link ist abgelaufen.',
    'checkYourEmail'     => 'Prüfen Sie Ihre E-Mail!',
    'magicLinkDetails'   => 'Wir haben Ihnen gerade eine E-Mail mit einem Login-Link geschickt. Er ist nur für {0} Minuten gültig.',
    'magicLinkDisabled'  => 'Die Verwendung von MagicLink ist derzeit nicht erlaubt.',
    'successLogout'      => 'Sie haben sich erfolgreich abgemeldet.',
    'backToLogin'        => 'Zurück zur Anmeldung',

    // Passwords
    'errorPasswordLength'       => 'Passwörter müssen mindestens {0, number} Zeichen lang sein.',
    'suggestPasswordLength'     => 'Passphrasen - bis zu 255 Zeichen lang - ergeben sicherere Passwörter, die leicht zu merken sind.',
    'errorPasswordCommon'       => 'Das Passwort darf kein allgemeines Passwort sein.',
    'suggestPasswordCommon'     => 'Das Passwort wurde mit über 65-tausend häufig verwendeten Passwörtern oder Passwörtern, die durch Hacks bekannt geworden sind, abgeglichen.',
    'errorPasswordPersonal'     => 'Passwörter dürfen keine gehashten persönlichen Informationen enthalten.',
    'suggestPasswordPersonal'   => 'Variationen Ihrer E-Mail-Adresse oder Ihres Benutzernamens sollten nicht für Passwörter verwendet werden.',
    'errorPasswordTooSimilar'   => 'Das Passwort ist dem Benutzernamen zu ähnlich.',
    'suggestPasswordTooSimilar' => 'Verwenden Sie keine Teile Ihres Benutzernamens in Ihrem Passwort.',
    'errorPasswordPwned'        => 'Das Passwort {0} wurde aufgrund einer Datenschutzverletzung aufgedeckt und wurde {1, number} Mal in {2} kompromittierten Passwörtern gesehen.',
    'suggestPasswordPwned'      => '{0} sollte niemals als Passwort verwendet werden. Wenn Sie es irgendwo verwenden, ändern Sie es sofort.',
    'errorPasswordEmpty'        => 'Ein Passwort ist erforderlich.',
    'errorPasswordTooLongBytes' => 'Das Passwort darf die Länge von {param} Bytes nicht überschreiten.',
    'passwordChangeSuccess'     => 'Passwort erfolgreich geändert',
    'userDoesNotExist'          => 'Passwort wurde nicht geändert. Der Benutzer existiert nicht',
    'resetTokenExpired'         => 'Tut mir leid. Ihr Reset-Token ist abgelaufen.',

    // Email Globals
    'emailInfo'      => 'Einige Informationen über die Person:',
    'emailIpAddress' => 'IP Adresse:',
    'emailDevice'    => 'Gerät:',
    'emailDate'      => 'Datum:',

    // 2FA
    'email2FATitle'       => 'Zwei-Faktor-Authentifizierung',
    'confirmEmailAddress' => 'Bestätigen Sie Ihre E-Mail-Adresse.',
    'emailEnterCode'      => 'Bestätigen Sie Ihre E-Mail',
    'emailConfirmCode'    => 'Geben Sie den 6-stelligen Code ein, den wir gerade an Ihre E-Mail-Adresse geschickt haben.',
    'email2FASubject'     => 'Ihr Authentifizierungscode',
    'email2FAMailBody'    => 'Ihr Authentifizierungscode lautet:',
    'invalid2FAToken'     => 'Der Code war falsch.',
    'need2FA'             => 'Sie müssen eine Zwei-Faktor-Verifizierung durchführen.',
    'needVerification'    => 'Überprüfen Sie Ihre E-Mail, um die Kontoaktivierung abzuschliessen.',

    // Activate
    'emailActivateTitle'    => 'E-Mail-Aktivierung',
    'emailActivateBody'     => 'Wir haben Ihnen gerade eine E-Mail mit einem Code zur Bestätigung Ihrer E-Mail-Adresse geschickt. Kopieren Sie diesen Code und fügen Sie ihn unten ein.',
    'emailActivateSubject'  => 'Ihr Aktivierungscode',
    'emailActivateMailBody' => 'Bitte verwenden Sie den unten stehenden Code, um Ihr Konto zu aktivieren und die Website zu nutzen.',
    'invalidActivateToken'  => 'Der Code war falsch.',
    'needActivate'          => 'Sie müssen Ihre Anmeldung abschliessen, indem Sie den an Ihre E-Mail-Adresse gesendeten Code bestätigen.',
    'activationBlocked'     => 'Bevor Sie sich anmelden können muss das Konto aktiviert werden.',

    // Groups
    'unknownGroup' => '{0} ist eine ungültige Gruppe.',
    'missingTitle' => 'Gruppen müssen einen Titel haben.',

    // Permissions
    'unknownPermission' => '{0} ist keine gültige Berechtigung.',

    // AGB
    'acceptAGBRequired' => 'Die AGB müssen akzeptiert werden.',


    'linkNotClickable' => 'Falls der Link in Ihrem E-Mail-Programm nicht klickbar ist, kopieren Sie bitte den folgenden Link in die Adresszeile Ihres Browsers:',

    'isUniqueEmail' => 'Wir betreiben mehrere Portale. Ihre E-Mail-Adresse ist bereits mit einem Konto verknüpft. Sie können sich mit Ihrer E-Mail-Adresse anmelden oder über den Passwort-Vergessen-Link direkt Zugriff auf Ihr bestehendes Konto anfordern.',


];
