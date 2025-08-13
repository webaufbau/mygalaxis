<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class SiteConfig extends BaseConfig
{
    // Basisvariablen
    public string $name = 'Offertenschweiz';
    public string $email = 'info@offertenschweiz.ch';
    public bool $testMode = false;
    public string $testEmail = 'testbenutzer@offertenschweiz.ch';
    public string $frontendUrl = 'https://offertenschweiz.ch';
    public string $backendUrl = '';
    public string $thankYouUrl = '';
    public string $logoUrl = '';
    public string $faviconUrl = '';
    public string $companyUidCheck = '';
    public string $phoneCheck = '';

    /**
     * Meta-Definition für die Felder
     */
    public array $fields = [
        'name' => [
            'type' => 'text',
            'label' => 'Seitenname',
            'placeholder' => 'Name der Webseite',
        ],
        'email' => [
            'type' => 'email',
            'label' => 'Standard E-Mail',
        ],
        'thankYouUrl' => [
            'type' => 'url',
            'label' => 'Fallback Danke-Seite URL',
            'multilang' => true,
        ],
        'frontendUrl' => [
            'type' => 'url',
            'label' => 'Frontend URL',
        ],
        'backendUrl' => [
            'type' => 'url',
            'label' => 'Backend URL',
        ],
        'logoUrl' => [
            'type' => 'url',
            'label' => 'Verfikationsprozess Logo URL',
        ],
        'faviconUrl' => [
            'type' => 'file',
            'label' => 'Favicon URL',
        ],
        'testMode' => [
            'type' => 'checkbox',
            'label' => 'Testmodus aktivieren',
        ],
        'testEmail' => [
            'type' => 'email',
            'label' => 'Testmodus E-Mail',
        ],
        // Firmenprüfung
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

        // Telefonnummerprüfung
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
    ];
}
