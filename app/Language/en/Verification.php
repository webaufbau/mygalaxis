<?php

return [
    'title' => 'Verification Successful',
    'successMessage' => '✅ Your phone number has been successfully verified!',
    'continue' => 'Continue',

    // Gemeinsame Keys
    'confirmTitle' => 'Enter Code',
    'enterCode' => 'Enter verification code',
    'smsSending' => 'We are sending an SMS with your verification code to <strong id="sms-number">{phone}</strong>.',
    'callSending' => 'You will receive a call on <strong>{phone}</strong> in a few seconds with your verification code.',
    'codeLabel' => 'Verification Code',
    'submitCode' => 'Confirm Code',
    'changePhoneNote' => 'If the displayed phone number is incorrect or you didn\'t receive a code, please enter your correct phone number below and resubmit the form.',
    'note' => 'Note',
    'noteText' => 'Too many requests in a short time will be automatically blocked.',
    'phoneLabel' => 'Phone Number',
    'changePhone' => 'Edit Phone Number',

    // SMS-Status-Meldungen
    'smsDelivered' => '✅ SMS successfully delivered to {phone}.',
    'smsPending' => '⏳ SMS is being delivered... Please wait.',
    'smsNoResult' => '⏳ Status is being determined... Please wait.',
    'smsInvalidNumber' => '❌ SMS could not be delivered. Please check the number {phone}.',
    'smsError' => '❌ Error sending SMS: {error}',
    'smsUnknown' => 'ℹ️ Status: {status}. Please wait...',
    'smsConnectionError' => '⚠️ Connection error while retrieving SMS status. Please try again...',
    'smsFailed' => '❌ Status could not be determined. This may indicate the phone number is incorrect. If you didn\'t receive an SMS, please check the entered number and click "Edit Phone Number". <a href="/verification/confirm">Click here to request a new code.</a>',

    // Allgemeine Meldungen
    'phoneMissing' => 'Phone number is missing.',
    'phoneNotChanged' => 'The phone number was not changed.',
    'phoneRequired' => 'Please enter a phone number.',
    'errorSendingCode' => 'Error sending code.',
    'fixedLineOnlyCall' => 'Landline number: only call verification possible.',
    'chooseMethod' => 'Please select a verification method.',
    'invalidRequest' => 'Invalid request.',
    'wrongCode' => 'Incorrect code. Please try again.',
    'invalidVerificationLink' => 'Invalid verification link.',
    'invalidOrOldVerificationLink' => 'Invalid or expired verification link.',
    'alreadyVerified' => 'Offer already verified.',
    'noOfferFound' => 'No request found.',
    'smsVerificationCode' => 'Your verification code for {sitename} is: {code}',
    'callVerificationCode' => 'Your verification code for {sitename} is',

    // Optional für Weiterleitungen etc.
    'verificationSuccess' => '✅ Your phone number has been successfully verified!',

    // Resend messages
    'newCodeSentSms' => 'A new code has been sent via SMS.',
    'newCodeSentCall' => 'A new call has been initiated.',

];
