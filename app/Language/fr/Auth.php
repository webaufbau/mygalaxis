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
'registerTitle'               => 'S\'inscrire',
    'emailAddress'            => 'Adresse e-mail',
    'emailRequired'           => 'Veuillez saisir une adresse e-mail valide.',
    'password'                => 'Mot de passe',
    'passwordMinLength'       => 'Veuillez saisir un mot de passe d\'au moins 6 caractères.',
    'passwordConfirm'         => 'Confirmer le mot de passe',
    'passwordConfirmRequired' => 'Veuillez confirmer votre mot de passe.',
    'passwordsMismatch'       => 'Les mots de passe ne correspondent pas.',
    'companyDataTitle'        => 'Données de l\'entreprise',
    'companyName'             => 'Nom de l\'entreprise',
    'companyNameRequired'     => 'Veuillez saisir le nom de l\'entreprise.',
    'companyUid'              => 'Registre du commerce (UID)',
    'companyUidRequired'      => 'Veuillez saisir le numéro UID au format %s.',
    'companyStreet'           => 'Rue',
    'companyStreetRequired'   => 'Veuillez saisir la rue.',
    'companyZip'              => 'Code postal',
    'companyZipRequired'      => 'Veuillez saisir le code postal.',
    'companyCity'             => 'Localité',
    'companyCityRequired'     => 'Veuillez saisir la localité.',
    'companyPhone'            => 'Numéro de téléphone',
    'companyPhoneRequired'    => 'Veuillez saisir un numéro de téléphone valide au format %s.',
    'companyWebsite'          => 'Site web',
    'companyWebsiteRequired'  => 'Veuillez saisir une URL valide.',
    'contactPerson'           => 'Personne de contact',
    'contactPersonRequired'   => 'Veuillez saisir la personne de contact.',
    'selectAtLeastOneCategory'=> 'Please select at least one category.',
    'registerButton'          => 'S\'inscrire',

    // Messages
    'loginFailed' => 'Échec de la connexion',
    'messageInvalidEmail' => 'Veuillez saisir une adresse e-mail valide.',
    'resetLinkSentIfRegistered' => 'Si l\'adresse e-mail est enregistrée, vous recevrez un lien de réinitialisation.',
    'resetPasswordSubject' => 'Réinitialisation du mot de passe',
    'resetPasswordMessage' => "Bonjour,\n\nVeuillez cliquer sur le lien suivant pour réinitialiser votre mot de passe :\n%s\n\nLe lien est valable pendant 1 heure.",
    'enterEmail' => 'Veuillez saisir votre adresse e-mail.',
    'userNotFound' => 'Utilisateur non trouvé.',
    'adminOnly' => 'Cette connexion est réservée aux administrateurs. Veuillez utiliser la connexion normale pour les entreprises.',
    'invalidOrExpiredLink' => 'Lien invalide ou expiré.',
    'invalidRequest' => 'Requête invalide.',
    'passwordsDontMatch' => 'Les mots de passe ne correspondent pas.',
    'passwordChangedSuccess' => 'Mot de passe modifié avec succès. Vous pouvez maintenant vous connecter.',
    'emailAlreadyRegistered' => 'L\'adresse e-mail est déjà enregistrée.',
    'registrationSuccess' => 'Inscription réussie. Vous pouvez maintenant vous connecter.',

    // Exceptions
    'unknownAuthenticator'  => '{0} n\'est pas un authentificateur valide.',
    'unknownUserProvider'   => 'Impossible de déterminer le fournisseur d\'utilisateurs à utiliser.',
    'invalidUser'           => 'L\'utilisateur spécifié est introuvable.',
    'bannedUser'            => 'Connexion impossible, votre compte est actuellement suspendu.',
    'logOutBannedUser'      => 'Votre compte a été déconnecté et suspendu.',
    'badAttempt'            => 'Connexion échouée. Veuillez vérifier vos identifiants.',
    'noPassword'            => 'Impossible de valider un utilisateur sans mot de passe.',
    'invalidPassword'       => 'Connexion impossible. Veuillez vérifier votre mot de passe.',
    'noToken'               => 'Chaque requête doit contenir un jeton d\'accès dans l\'en-tête {0}.',
    'badToken'              => 'Jeton d\'accès invalide.',
    'oldToken'              => 'Le jeton d\'accès a expiré.',
    'noUserEntity'          => 'L\'entité utilisateur doit être spécifiée pour vérifier le mot de passe.',
    'invalidEmail'          => 'Impossible de vérifier si l\'adresse e-mail correspond à celle enregistrée.',
    'unableSendEmailToUser' => 'Un problème est survenu lors de l\'envoi de l\'e-mail. Impossible d\'envoyer un message à « {0} ».',
    'throttled'             => 'Trop de tentatives depuis cette adresse IP. Veuillez réessayer dans {0} secondes.',
    'notEnoughPrivilege'    => 'Vous n\'avez pas les autorisations nécessaires pour effectuer cette opération.',

    // JWT Exceptions
    'invalidJWT'     => 'Le jeton est invalide.',
    'expiredJWT'     => 'Le jeton a expiré.',
    'beforeValidJWT' => 'Le jeton n\'est pas encore valide.',

    'email'           => 'Adresse e-mail',
    'username'        => 'Nom d\'utilisateur',
    //'password'        => 'Passwort',
    //'passwordConfirm' => 'Passwort (erneut)',
    'haveAccount'     => 'Vous avez déjà un compte ?',
    'token'           => 'Jeton',

    // Buttons
    'confirm' => 'Confirmer',
    'send'    => 'Envoyer',

    // Registration
    'register'         => 'S\'inscrire',
    'registerDisabled' => 'L\'inscription est actuellement désactivée.',
    'registerSuccess'  => 'Bienvenue à bord !',

    // Login
    'login'              => 'Connexion',
    'needAccount'        => 'Vous n\'avez pas encore de compte?',
    'rememberMe'         => 'Rester connecté',
    'forgotPassword'     => 'Mot de passe oublié?',
    'useMagicLink'       => 'Utiliser un lien magique pour se connecter',
    'magicLinkSubject'   => 'Votre lien de connexion',
    'magicTokenNotFound' => 'Impossible de vérifier le lien.',
    'magicLinkExpired'   => 'Désolé, le lien a expiré.',
    'checkYourEmail'     => 'Vérifiez votre e-mail!',
    'magicLinkDetails'   => 'Nous vous avons envoyé un e-mail avec un lien de connexion. Il est valable pendant {0} minutes.',
    'magicLinkDisabled'  => 'L\'authentification par lien magique est actuellement désactivée.',
    'successLogout'      => 'Vous vous êtes déconnecté avec succès.',
    'backToLogin'        => 'Retour à la connexion',

    // Passwords
    'errorPasswordLength'       => 'Les mots de passe doivent contenir au moins {0, number} caractères.',
    'suggestPasswordLength'     => 'Les phrases de passe — jusqu\'à 255 caractères — sont plus sûres et faciles à retenir.',
    'errorPasswordCommon'       => 'Le mot de passe ne doit pas être trop courant.',
    'suggestPasswordCommon'     => 'Le mot de passe a été comparé à plus de 65 000 mots de passe fréquemment utilisés ou connus lors de violations de données.',
    'errorPasswordPersonal'     => 'Les mots de passe ne doivent pas contenir d\'informations personnelles hachées.',
    'suggestPasswordPersonal'   => 'N\'utilisez pas de variantes de votre adresse e-mail ou nom d\'utilisateur dans vos mots de passe.',
    'errorPasswordTooSimilar'   => 'Le mot de passe est trop similaire au nom d\'utilisateur.',
    'suggestPasswordTooSimilar' => 'N\'utilisez pas de parties de votre nom d\'utilisateur dans votre mot de passe.',
    'errorPasswordPwned'        => 'Le mot de passe {0} a été compromis lors d\'une violation de données et a été vu {1, number} fois dans {2} mots de passe compromis.',
    'suggestPasswordPwned'      => '{0} ne doit jamais être utilisé comme mot de passe. Si vous l\'utilisez ailleurs, changez-le immédiatement.',
    'errorPasswordEmpty'        => 'Un mot de passe est requis.',
    'errorPasswordTooLongBytes' => 'Le mot de passe ne peut pas dépasser {param} octets.',
    'passwordChangeSuccess'     => 'Mot de passe changé avec succès.',
    'userDoesNotExist'          => 'Le mot de passe n\'a pas été modifié. L\'utilisateur n\'existe pas.',
    'resetTokenExpired'         => 'Désolé. Le jeton de réinitialisation a expiré.',

    // Email Globals
    'emailInfo'      => 'Quelques informations sur la personne :',
    'emailIpAddress' => 'Adresse IP :',
    'emailDevice'    => 'Appareil :',
    'emailDate'      => 'Date :',

    // 2FA
    'email2FATitle'       => 'Authentification à deux facteurs',
    'confirmEmailAddress' => 'Confirmez votre adresse e-mail.',
    'emailEnterCode'      => 'Confirmez votre e-mail',
    'emailConfirmCode'    => 'Entrez le code à 6 chiffres que nous venons d\'envoyer à votre adresse e-mail.',
    'email2FASubject'     => 'Votre code d\'authentification',
    'email2FAMailBody'    => 'Votre code d\'authentification est :',
    'invalid2FAToken'     => 'Le code est incorrect.',
    'need2FA'             => 'Vous devez effectuer une vérification à deux facteurs.',
    'needVerification'    => 'Veuillez vérifier votre e-mail pour finaliser l\'activation du compte.',

    // Activate
    'emailActivateTitle'    => 'Activation de l\'e-mail',
    'emailActivateBody'     => 'Nous vous avons envoyé un e-mail avec un code pour confirmer votre adresse. Copiez ce code et collez-le ci-dessous.',
    'emailActivateSubject'  => 'Votre code d\'activation',
    'emailActivateMailBody' => 'Veuillez utiliser le code ci-dessous pour activer votre compte et accéder au site.',
    'invalidActivateToken'  => 'Le code est incorrect.',
    'needActivate'          => 'Vous devez finaliser votre inscription en confirmant le code envoyé à votre adresse e-mail.',
    'activationBlocked'     => 'Votre compte doit être activé avant de pouvoir vous connecter.',

    // Groups
    'unknownGroup' => '{0} est un groupe invalide.',
    'missingTitle' => 'Les groupes doivent avoir un titre.',

    // Permissions
    'unknownPermission' => '{0} n\'est pas une autorisation valide.',

    // AGB
    'acceptAGBRequired' => 'Les CGV doivent être acceptées.',


    'linkNotClickable' => 'If the link is not clickable in your email client, please copy and paste the following link into your browser:',

    'isUniqueEmail' => 'Nous exploitons plusieurs portails. Votre adresse e-mail est déjà associée à un compte. Vous pouvez vous connecter avec votre e-mail ou demander un accès direct à votre compte existant via le lien magique.',

];
