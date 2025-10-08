<?php

return [
    'title'           => 'Finance',
    'currentBalance'  => 'Current Balance',
    'amountCHF'       => 'Amount (CHF)',
    'topupButton'     => 'Top up balance',
    'year'            => 'Year',
    'allYears'        => 'All years',
    'month'           => 'Month',
    'showButton'      => 'Show',
    'pdfExport'       => 'PDF Export',
    'noBookings'      => 'No transactions found.',
    'noBookingsForMonth' => 'No transactions found for this month.',
    'date'            => 'Date',
    'type'            => 'Type',
    'description'     => 'Description',
    'amount'          => 'Amount',

    // Fehlermeldungen & Benachrichtigungen
    'messageInvalidAmount' => 'Invalid amount.',
    'messageCreditAdded' => 'Credit successfully added.',
    'messageTransactionNotFound' => 'Transaction not found.',
    'messagePaymentSuccess' => 'Payment successful.',
    'messagePaymentMethodSaved' => 'Payment method saved.',
    'messagePaymentMethodDeleted' => 'Payment method deleted.',
    'errorIncompleteAddress' => 'Your address is incomplete or invalid. Please correct it to top up your balance.',
    'errorPaymentFailed' => 'Payment failed.',
    'errorPaymentNotAuthorized' => 'Payment not authorized.',
    'errorPaymentCheck' => 'Error checking payment. Please try again later or contact support.',
    'errorAmountExceeded' => 'The payment was declined due to insufficient available balance or credit limit.',
    'errorPaymentPageNotCreated' => 'Payment page could not be created.',
    'errorNoTokenReceived' => 'No token received.',
    'errorNotFoundOrDenied' => 'Not found or access denied.',

    // PDF
    'pdf_title'    => 'Transactions',

    // Monthly Invoice
    'monthlyInvoice' => 'Monthly Invoice',
    'invoiceDate' => 'Invoice Date',
    'invoiceNumber' => 'Invoice Number',
    'billingPeriod' => 'Billing Period',
    'bookingNr' => 'Booking No.',
    'totalAmount' => 'Total Amount',
    'customer' => 'Customer',
    'invoice' => 'Invoice',
    'paymentNote' => 'This invoice has already been fully paid in advance via Saferpay.',
    'thankYou' => 'Thank you for your purchase and your trust.',

    // VAT and Bank Details
    'vatNumber' => 'VAT Number',
    'amountExclVat' => 'Amount excl. VAT',
    'vat' => 'VAT',
    'amountInclVat' => 'Amount incl. VAT',
    'serviceDate' => 'Service Date',
    'servicePeriod' => 'Service Period',
    'bankDetails' => 'Bank Details',
    'iban' => 'IBAN',
    'bank' => 'Bank',
    'contact' => 'Contact',

    // Für topupFail View
    'topupFailTitle'   => 'Payment failed',
    'topupFailMessage' => 'Unfortunately, your top-up could not be completed.',
    'backToTopup'      => 'Back to top-up',

    // Sonstige
    'topupDescription' => 'Top-up via',
    'onlinePayment'    => 'Online payment',

    // Top-up Page
    'topupTitle' => 'Top up balance',
    'insufficientBalance' => 'Your balance is insufficient for this purchase.',
    'requiredAmount' => 'Required amount',
    'missingAmount' => 'Missing amount',
    'topupAmount' => 'Top-up amount (CHF)',
    'minimumTopupAmount' => 'Minimum top-up: %s CHF',
    'topupNow' => 'Top up now',
    'backToOffers' => 'Back to offers',
];
