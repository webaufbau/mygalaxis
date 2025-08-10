<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class SiteConfig extends BaseConfig
{
    public string $name = 'Offertenschweiz';
    public string $email = 'info@offertenschweiz.ch';
    public bool $testMode = false;
    public string $testEmail = 'testbenutzer@offertenschweiz.ch';
    public string $frontendUrl = 'https://offertenschweiz.ch';

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
            'label' => 'Danke-Seite URL',
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
            'label' => 'Logo URL',
        ],
        'testMode' => [
            'type' => 'checkbox',
            'label' => 'Testmodus aktivieren',
        ],
        'testEmail' => [
            'type' => 'email',
            'label' => 'Testmodus E-Mail',
        ],
    ];

}
