<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class SiteConfig extends BaseConfig
{
    // Basisvariablen
    public string $name = 'Offertenschweiz';
    public string $email = 'info@offertenschweiz.ch';
    public string $domain_extension = '.ch';
    public string $address = '';
    public string $emailSignature = '';
    public bool $testMode = false;
    public string $testEmail = 'testbenutzer@offertenschweiz.ch';
    public string $frontendUrl = 'https://offertenschweiz.ch';
    public string $backendUrl = '';
    public array $thankYouUrl = [];
    public string $logoUrl = '';
    public string $faviconUrl = '';
    public string $companyUidCheck = '';
    public string $phoneCheck = '';
    public string $siteCountry = '';
    public bool $vatEnabled = false;
    public float $vatRate = 8.1;
    public string $vatExemptionText = '';
    public string $bankIban = '';
    public string $bankName = '';
    public int $phoneVerificationValidityHours = 24;
    public bool $enableMoveCleaningCombo = false; // Umzug + Reinigung Kombi-Offerten aktivieren
    /**
     * Gruppen-Definition für die Settings-Tabs
     */
    public array $fieldGroups = [
        'general' => [
            'label' => 'Allgemein',
            'icon' => 'bi-gear',
            'fields' => ['name', 'email', 'domain_extension', 'address', 'frontendUrl', 'backendUrl', 'thankYouUrl'],
        ],
        'appearance' => [
            'label' => 'Erscheinungsbild',
            'icon' => 'bi-palette',
            'fields' => ['logoUrl', 'logoHeightPixel', 'faviconUrl', 'headerBackgroundColor'],
        ],
        'email' => [
            'label' => 'E-Mail',
            'icon' => 'bi-envelope',
            'fields' => ['emailSignature', 'testMode', 'testEmail'],
        ],
        'registration' => [
            'label' => 'Registrierung',
            'icon' => 'bi-person-plus',
            'fields' => ['siteCountry', 'companyUidCheck', 'phoneCheck'],
        ],
        'finance' => [
            'label' => 'Finanzen',
            'icon' => 'bi-cash-coin',
            'fields' => ['vatEnabled', 'vatRate', 'vatExemptionText', 'bankIban', 'bankName'],
        ],
    ];

    /**
     * Meta-Definition für die Felder
     */
    public array $fields = [
        // Allgemein
        'name' => [
            'type' => 'text',
            'label' => 'Seitenname',
            'placeholder' => 'Name der Webseite',
        ],
        'email' => [
            'type' => 'email',
            'label' => 'Standard E-Mail',
        ],
        'domain_extension' => [
            'type' => 'text',
            'label' => 'Domain Extension für E-Mail Absendername',
            'placeholder' => '.ch',
            'help' => 'Wird an den Absendernamen angehängt (z.B. "Offertenschweiz.ch")',
        ],
        'address' => [
            'type' => 'textarea',
            'label' => 'Adressdaten Website',
            'placeholder' => 'Für Rechnungskopf',
        ],
        'frontendUrl' => [
            'type' => 'url',
            'label' => 'Frontend URL',
        ],
        'backendUrl' => [
            'type' => 'url',
            'label' => 'Backend URL',
        ],
        'thankYouUrl' => [
            'type' => 'url',
            'label' => 'Fallback Danke-Seite URL',
            'multilang' => true,
        ],

        // Erscheinungsbild
        'logoUrl' => [
            'type' => 'file',
            'label' => 'Verfikationsprozess Logo',
        ],
        'logoHeightPixel' => [
            'type' => 'text',
            'label' => 'Verfikationsprozess Logo Höhe in Pixel',
        ],
        'faviconUrl' => [
            'type' => 'file',
            'label' => 'Favicon URL',
        ],
        'headerBackgroundColor' => [
            'type' => 'color',
            'label' => 'Primärfarbe Website (Login, Register, Magic-Link, Verifikation)',
        ],

        // E-Mail
        'emailSignature' => [
            'type' => 'textarea',
            'label' => 'E-Mail-Signatur Text',
            'placeholder' => '',
        ],
        'testMode' => [
            'type' => 'checkbox',
            'label' => 'Testmodus aktivieren',
            'help' => 'Wenn aktiviert, werden alle E-Mails an die Testmodus E-Mail gesendet.',
        ],
        'testEmail' => [
            'type' => 'email',
            'label' => 'Testmodus E-Mail',
        ],

        // Registrierung
        'siteCountry' => [
            'type' => 'dropdown',
            'label' => 'Welches Land bei Registrierungen setzen?',
            'options' => [
                'ch'  => 'Schweiz',
                'de'  => 'Deutschland',
                'at'  => 'Österreich',
            ],
            'default' => 'ch'
        ],
        'companyUidCheck' => [
            'type' => 'dropdown',
            'label' => 'Welche Prüfung für Firmen?',
            'options' => [
                ''    => 'Keine',
                'ch'  => 'Schweiz nach Zefix',
                'de'  => 'Deutschland nach Handelsregister / EUID',
                'at'  => 'Österreich nach Firmenbuch',
            ],
            'default' => ''
        ],
        'phoneCheck' => [
            'type' => 'dropdown',
            'label' => 'Welche Prüfung für Telefonnummer?',
            'options' => [
                ''    => 'Keine',
                'ch'  => 'CH Format',
                'de'  => 'DE Format',
                'at'  => 'AT Format',
            ],
            'default' => ''
        ],

        // Finanzen
        'vatEnabled' => [
            'type' => 'checkbox',
            'label' => 'Mehrwertsteuer aktivieren',
        ],
        'vatRate' => [
            'type' => 'text',
            'label' => 'MWST-Satz in %',
            'placeholder' => '8.1',
        ],
        'vatExemptionText' => [
            'type' => 'textarea',
            'label' => 'MWST-Befreiungstext (wenn nicht MWST-pflichtig)',
            'placeholder' => 'Von der Mehrwertsteuer befreit (Kleinunternehmer gemäss Art. 10 Abs. 2 MWSTG).',
        ],
        'bankIban' => [
            'type' => 'text',
            'label' => 'IBAN',
            'placeholder' => 'CH93 0076 2011 6238 5295 7',
        ],
        'bankName' => [
            'type' => 'text',
            'label' => 'Bankname',
            'placeholder' => 'PostFinance AG',
        ],

        // Telefon-Verifizierung (vorerst deaktiviert)
        // 'phoneVerificationValidityHours' => [
        //     'type' => 'text',
        //     'label' => 'Gültigkeitsdauer Telefon-Verifizierung (Stunden)',
        //     'placeholder' => '24',
        //     'help' => 'Nach wie vielen Stunden muss eine Telefonnummer erneut verifiziert werden? (Standard: 24 Stunden)',
        // ],

        // Kombi-Offerten (vorerst deaktiviert)
        // 'enableMoveCleaningCombo' => [
        //     'type' => 'checkbox',
        //     'label' => 'Umzug + Reinigung Kombi-Offerten aktivieren',
        //     'help' => 'Wenn aktiviert, werden Umzug und Reinigung Anfragen der gleichen Person zu einer Kombi-Offerte zusammengefasst.',
        // ],
    ];
}
