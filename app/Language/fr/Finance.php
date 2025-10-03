<?php

return [
    'title'           => 'Finances',
    'currentBalance'  => 'Solde actuel',
    'amountCHF'       => 'Montant (CHF)',
    'topupButton'     => 'Recharger le solde',
    'year'            => 'Année',
    'allYears'        => 'Toutes les années',
    'month'           => 'Mois',
    'showButton'      => 'Afficher',
    'pdfExport'       => 'Export PDF',
    'noBookings'      => 'Aucune transaction trouvée.',
    'noBookingsForMonth' => 'Aucune transaction trouvée pour ce mois.',
    'date'            => 'Date',
    'type'            => 'Type',
    'description'     => 'Description',
    'amount'          => 'Montant',

    // Fehlermeldungen & Benachrichtigungen
    'messageInvalidAmount' => 'Montant invalide.',
    'messageCreditAdded' => 'Crédit rechargé avec succès.',
    'messageTransactionNotFound' => 'Transaction non trouvée.',
    'messagePaymentSuccess' => 'Paiement réussi.',
    'messagePaymentMethodSaved' => 'Méthode de paiement enregistrée.',
    'messagePaymentMethodDeleted' => 'Méthode de paiement supprimée.',
    'errorIncompleteAddress' => 'Votre adresse est incomplète ou invalide. Veuillez la corriger pour recharger votre solde.',
    'errorPaymentFailed' => 'Échec du paiement',
    'errorPaymentNotAuthorized' => 'Paiement non autorisé.',
    'errorPaymentCheck' => 'Erreur lors de la vérification du paiement. Veuillez réessayer plus tard ou contacter le support.',
    'errorAmountExceeded' => 'Le paiement a été refusé car le solde disponible ou la limite de crédit est insuffisante.',
    'errorPaymentPageNotCreated' => 'La page de paiement n\'a pas pu être créée.',
    'errorNoTokenReceived' => 'Aucun jeton reçu.',
    'errorNotFoundOrDenied' => 'Non trouvé ou accès refusé.',

    // PDF
    'pdf_title'    => 'Transactions',

    // Monatrechnung
    'monthlyInvoice' => 'Facture mensuelle',
    'invoiceDate' => 'Date de facturation',
    'invoiceNumber' => 'Numéro de facture',
    'billingPeriod' => 'Période de facturation',
    'bookingNr' => 'N° de réservation',
    'totalAmount' => 'Montant total',
    'customer' => 'Client',
    'invoice' => 'Facture',
    'paymentNote' => 'Cette facture a déjà été entièrement payée à l\'avance via Saferpay.',
    'thankYou' => 'Nous vous remercions pour votre achat et votre confiance.',

    // Für topupFail View
    'topupFailTitle'   => 'Échec du paiement',
    'topupFailMessage' => 'Votre recharge n\'a malheureusement pas pu être finalisée.',
    'backToTopup'      => 'Retour à la recharge',

    // Sonstige
    'topupDescription' => 'Recharge de solde via',
    'onlinePayment'    => 'Paiement en ligne',
];
