<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var list<string>
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
        \App\Validation\MyGalaxisRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------


    //--------------------------------------------------------------------
    // Rules For Registration
    //--------------------------------------------------------------------
    public $registration = [
        'company_name' => [
            'label' => 'Auth.company_name',
            'rules' => [
                'required',
                'max_length[150]',
                'min_length[1]',
            ],
        ],
        'contact_person' => [
            'label' => 'Auth.contact_person',
            'rules' => [
                'required',
                'max_length[150]',
                'min_length[1]',
            ],
        ],
        /*'company_uid' => [
            'label' => 'Auth.company_uid',
            'rules' => [
                'required',
                'max_length[150]',
                'min_length[1]',
            ],
        ],*/
        'company_street' => [
            'label' => 'Auth.company_street',
            'rules' => [
                'required',
                'max_length[150]',
                'min_length[1]',
            ],
        ],
        'company_zip' => [
            'label' => 'Auth.company_zip',
            'rules' => [
                'max_length[4]',
                'min_length[4]',
            ],
        ],
        'company_city' => [
            'label' => 'Auth.company_city',
            'rules' => [
                'required',
                'max_length[150]',
                'min_length[1]',
            ],
        ],
        'company_phone' => [
            'label' => 'Auth.company_phone',
            'rules' => [
                'required',
                'max_length[20]',  // typische maximale Länge für Telefonnummern
                'min_length[10]',  // minimale Länge für eine gültige Telefonnummer
                'regex_match[/^\+?[0-9\s\-\(\)]+$/]'  // erlaubt Zahlen, Leerzeichen, Bindestriche, Klammern und ein optionales führendes +
            ],
            'errors' => [
                'regex_match' => 'Das {field}-Feld muss eine gültige Telefonnummer enthalten.'
            ]
        ],
        'company_website' => [
            'label' => 'Auth.company_website',
            'rules' => [
            ],
        ],
        'email' => [
            'label' => 'Auth.email',
            'rules' => [
                'required',
                'max_length[254]',
                'valid_email',
                'rules' => 'required|valid_email|emailUniqueWithPortal',
                'errors' => [
                    'emailUniqueWithPortal' => 'Auth.isUniqueEmail', // nur Key, keine lang()!
                ],
            ],
        ],
        'password' => [
            'label' => 'Auth.password',
            'rules' => 'required|max_byte[72]|strong_password[]',
            'errors' => [
                'max_byte' => 'Auth.errorPasswordTooLongBytes'
            ]
        ],
    ];


    //--------------------------------------------------------------------
    // Rules For Login
    //--------------------------------------------------------------------
    public $login = [
        // 'username' => [
        //     'label' => 'Auth.username',
        //     'rules' => [
        //         'required',
        //         'max_length[30]',
        //         'min_length[3]',
        //         'regex_match[/\A[a-zA-Z0-9\.]+\z/]',
        //     ],
        // ],
        'email' => [
            'label' => 'Auth.email',
            'rules' => [
                'required',
                'max_length[254]',
                'valid_email'
            ],
        ],
        'password' => [
            'label' => 'Auth.password',
            'rules' => [
                'required',
                'max_byte[72]',
            ],
            'errors' => [
                'max_byte' => 'Auth.errorPasswordTooLongBytes',
            ]
        ],
    ];


    //--------------------------------------------------------------------
    // Rules For Password
    //--------------------------------------------------------------------
    public $password = [
        'password' => [
            'label' => 'Auth.password',
            'rules' => [
                'required',
                'max_byte[72]',
            ],
            'errors' => [
                'max_byte' => 'Auth.errorPasswordTooLongBytes',
            ]
        ],
        'password_repeat' => [
            'label' => 'Auth.password',
            'rules' => [
                'required',
                'max_byte[72]',
                'matches[password]',
            ],
            'errors' => [
                'max_byte' => 'Auth.errorPasswordTooLongBytes',
                'matches' => 'Das Feld Passwort stimmt nicht mit dem Feld Passwort wiederholen überein.'
            ]
        ],
    ];
}
