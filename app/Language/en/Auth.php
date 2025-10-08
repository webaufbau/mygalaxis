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
    'registerTitle'           => 'Register',
    'emailAddress'            => 'Email address',
    'emailRequired'           => 'Please enter a valid email address.',
    'password'                => 'Password',
    'passwordMinLength'       => 'Please enter a password with at least 6 characters.',
    'passwordConfirm'         => 'Confirm password',
    'passwordConfirmRequired' => 'Please confirm your password.',
    'passwordsMismatch'       => 'Passwords do not match.',
    'companyDataTitle'        => 'Company details',
    'companyName'             => 'Company name',
    'companyNameRequired'     => 'Please enter the company name.',
    'companyUid'              => 'Company registration number (UID)',
    'companyUidRequired'      => 'Please enter the UID in the format %s.',
    'companyStreet'           => 'Street',
    'companyStreetRequired'   => 'Please enter the street address.',
    'companyZip'              => 'ZIP code',
    'companyZipRequired'      => 'Please enter the ZIP code.',
    'companyCity'             => 'City',
    'companyCityRequired'     => 'Please enter the city.',
    'companyPhone'            => 'Phone number',
    'companyPhoneRequired'    => 'Please enter a valid phone number in the format %s.',
    'companyWebsite'          => 'Website',
    'companyWebsiteRequired'  => 'Please enter a valid URL.',
    'contactPerson'           => 'Contact person',
    'contactPersonRequired'   => 'Please enter the contact person.',
    'selectAtLeastOneCategory'=> 'Please select at least one category.',
    'registerButton'          => 'Register',

    // Messages
    'loginFailed' => 'Login failed',
    'messageInvalidEmail' => 'Please enter a valid email address.',
    'resetLinkSentIfRegistered' => 'If the email is registered, you will receive a link to reset your password.',
    'resetPasswordSubject' => 'Reset password',
    'resetPasswordMessage' => "Hello,\n\nPlease click the following link to reset your password:\n%s\n\nThe link is valid for 1 hour.",
    'enterEmail' => 'Please enter your email address.',
    'userNotFound' => 'User not found.',
    'adminOnly' => 'This login is for administrators only. Please use the regular login for companies.',
    'invalidOrExpiredLink' => 'Invalid or expired link.',
    'invalidRequest' => 'Invalid request.',
    'passwordsDontMatch' => 'Passwords do not match.',
    'passwordChangedSuccess' => 'Password successfully changed. You can now log in.',
    'emailAlreadyRegistered' => 'We operate multiple portals (Renovo24, Offertenheld, Offertenschweiz). Your email address is already linked to an account. You can log in with your email address and existing password or use the forgot password link to access your existing account.',
    'registrationSuccess' => 'Registration successful. You can now log in.',

    // Exceptions
    'unknownAuthenticator'  => '{0} is not a valid authenticator.',
    'unknownUserProvider'   => 'Unable to determine the User Provider to use.',
    'invalidUser'           => 'The specified user could not be found.',
    'bannedUser'            => 'Login not possible because your account is currently banned.',
    'logOutBannedUser'      => 'You have been logged out and your account has been banned.',
    'badAttempt'            => 'Login failed. Please check your credentials.',
    'noPassword'            => 'Cannot validate a user without a password.',
    'invalidPassword'       => 'Login failed. Please check your password.',
    'noToken'               => 'Each request must include a bearer token in the {0} header.',
    'badToken'              => 'The access token is invalid.',
    'oldToken'              => 'The access token has expired.',
    'noUserEntity'          => 'The user entity must be provided for password validation.',
    'invalidEmail'          => 'Could not verify that the email matches the stored email address.',
    'unableSendEmailToUser' => 'There was a problem sending the email. We could not send an email to "{0}".',
    'throttled'             => 'Too many requests from this IP address. Please try again in {0} seconds.',
    'notEnoughPrivilege'    => 'You do not have sufficient permission to perform the requested action.',

    // JWT Exceptions
    'invalidJWT'     => 'The token is invalid.',
    'expiredJWT'     => 'The token has expired.',
    'beforeValidJWT' => 'The token is not yet valid.',

    'email'           => 'Email address',
    'username'        => 'Username',
    //'password'        => 'Passwort',
    //'passwordConfirm' => 'Passwort (erneut)',
    'haveAccount'     => 'Already have an account?',
    'token'           => 'Token',

    // Buttons
    'confirm' => 'Confirm',
    'send'    => 'Send',

    // Registration
    'register'         => 'Register',
    'registerDisabled' => 'Registration is currently not allowed.',
    'registerSuccess'  => 'Welcome on board!',

    // Login
    'login'              => 'Login',
    'needAccount'        => 'Need an account?',
    'rememberMe'         => 'Keep me logged in',
    'forgotPassword'     => 'Forgot password?',
    'useMagicLink'       => 'Use a login link',
    'magicLinkSubject'   => 'Your login link',
    'magicTokenNotFound' => 'The link could not be verified.',
    'magicLinkExpired'   => 'Sorry, the link has expired.',
    'checkYourEmail'     => 'Check your email!',
    'magicLinkDetails'   => 'We just sent you an email with a login link. It\'s only valid for {0} minutes.',
    'magicLinkDisabled'  => 'The use of MagicLink is currently not allowed.',
    'successLogout'      => 'You have successfully logged out.',
    'backToLogin'        => 'Back to login',

    // Passwords
    'errorPasswordLength'       => 'Passwords must be at least {0, number} characters long.',
    'suggestPasswordLength'     => 'Passphrases – up to 255 characters long – provide more secure and memorable passwords.',
    'errorPasswordCommon'       => 'The password must not be a common password.',
    'suggestPasswordCommon'     => 'The password has been compared against over 65,000 commonly used or previously compromised passwords.',
    'errorPasswordPersonal'     => 'Passwords must not contain hashed personal information.',
    'suggestPasswordPersonal'   => 'Variations of your email address or username should not be used in passwords.',
    'errorPasswordTooSimilar'   => 'The password is too similar to the username.',
    'suggestPasswordTooSimilar' => 'Do not use parts of your username in your password.',
    'errorPasswordPwned'        => 'The password {0} was found in a data breach and has appeared {1, number} times in {2} compromised passwords.',
    'suggestPasswordPwned'      => '{0} should never be used as a password. If you use it anywhere, change it immediately.',
    'errorPasswordEmpty'        => 'A password is required.',
    'errorPasswordTooLongBytes' => 'The password must not exceed {param} bytes in length.',
    'passwordChangeSuccess'     => 'Password successfully changed.',
    'userDoesNotExist'          => 'Password was not changed. The user does not exist.',
    'resetTokenExpired'         => 'Sorry, your reset token has expired.',

    // Email Globals
    'emailInfo'      => 'Some information about the person:',
    'emailIpAddress' => 'IP address:',
    'emailDevice'    => 'Device:',
    'emailDate'      => 'Date:',

    // 2FA
    'email2FATitle'       => 'Two-Factor Authentication',
    'confirmEmailAddress' => 'Confirm your email address.',
    'emailEnterCode'      => 'Verify your email',
    'emailConfirmCode'    => 'Enter the 6-digit code we just sent to your email address.',
    'email2FASubject'     => 'Your authentication code',
    'email2FAMailBody'    => 'Your authentication code is:',
    'invalid2FAToken'     => 'The code was incorrect.',
    'need2FA'             => 'You must complete two-factor verification.',
    'needVerification'    => 'Check your email to complete account activation.',

    // Activate
    'emailActivateTitle'    => 'Email Activation',
    'emailActivateBody'     => 'We\'ve just sent you an email with a code to confirm your email address. Copy and paste the code below.',
    'emailActivateSubject'  => 'Your activation code',
    'emailActivateMailBody' => 'Please use the code below to activate your account and access the site.',
    'invalidActivateToken'  => 'The code was incorrect.',
    'needActivate'          => 'You must complete your registration by confirming the code sent to your email address.',
    'activationBlocked'     => 'You must activate your account before you can log in.',

    // Groups
    'unknownGroup' => '{0} is an invalid group.',
    'missingTitle' => 'Groups must have a title.',

    // Permissions
    'unknownPermission' => '{0} is not a valid permission.',

    // AGB
    'acceptAGBRequired' => 'The terms and conditions must be accepted.',


    'linkNotClickable' => 'If the link is not clickable in your email client, please copy and paste the following link into your browser:',

    'isUniqueEmail' => 'We operate multiple portals. Your email address is already linked to an account. You can log in with your email or request direct access to your existing account via the magic link.',

];
