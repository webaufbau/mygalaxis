<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class FormFieldOptions extends BaseConfig
{
    /**
     * Felder, die ein erklärendes Bild besitzen.
     */
    public array $fieldsWithImages = [
        'fensterart_01',
        'fensterart_02',
        'fensterart_03',
        'fensterart_04',
        'fensterart_05',
        'fensterart_06',
        'fensterart_07',
        'fensterart_08',
        'fensterart_09',
        'fensterart_010',
        'fensterart_011',
        'fensterart_012',

        'rollaeden_rollaeden',
        'rollaeden_storen',

        'heizkoerper_gerillt',
        'heizkoerper_flach',
        'heizkoerper_konvektor',

        'kuechen_01',
        'kuechen_02',
        'kuechen_03',
        'kuechen_04',
        'kuechen_05',
        'kuechen_06',
        'kuechen_07',
        'kuechen_08',
        'kuechen_09',

        'fensterlaeden_normal_ja1',
        'fensterlaeden_normal_ja2',

        'zaun_a_d',
        'zaun_e_h',
        'zaun_i_l',
        'zaun_m_p',
        'zaun_q_t',

        'heizung_gerillt',
        'heizung_flach',
        'heizung_konv',

        'bodenplatten_typ_a_d',
        'bodenplatten_typ_e_h',
        'bodenplatten_typ_i_l',
        'bodenplatten_typ_m_p',
        'bodenplatten_typ_q_t',

        'kies_typ_a_d',
        'kies_typ_e_h',
        'kies_typ_i_l',

        'mauer_typ_a_d',
        'mauer_typ_e_h',
        'mauer_typ_i_l',
        'mauer_typ_m_p',

        'diele_a_d',
        'diele_e_h',
        'diele_i_l',
        'diele_m_p',

        'teich_a_d',
        'teich_e_h',
        'teich_i_l',

        'hecke_thuja',
        'hecke_kirschlorbeer',
        'hecke_glanzmispel',
        'hecke_buchshecke',
        'hecke_goldliguster',
        'hecke_eibenhecke',
        'hecke_stechpalme',
        'hecke_zypresse',
        'hecke_haselnuss',
        'hecke_andere',
    ];

    /**
     * Basis-URL zu den erklärenden Bildern.
     */
    public string $imageBaseUrl = 'https://offertenschweiz.ch/wp-content/uploads/';

    /**
     * Felder, die IMMER ausgeblendet werden (technische Felder, UTM-Parameter, etc.)
     * Diese Felder werden niemals in Emails oder Offerten-Details angezeigt.
     */
    public array $excludedFieldsAlways = [
        // Technische Felder
        'terms_n_condition',
        'terms_and_conditions',
        'terms',
        'type',
        'lang',
        'language',
        'csrf_test_name',
        'submit',
        'form_token',
        '__submission',
        '__fluent_form_embded_post_id',
        '_wp_http_referer',
        'form_name',
        'uuid',
        'service_url',
        'uuid_value',
        'verified_method',
        'additional_service',
        'referrer',

        // Fluent Forms Felder
        'fluentformnonce',
        '_fluentformnonce',
        'input_type',

        // UTM Parameter
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',

        // Interne Steuerungsfelder
        'skip_kontakt',
        'skip_reinigung_umzug',
        'session',
        'request_session_id',

        // HINWEIS: Gartenbau-Felder werden nun über FieldDisplayRules Config gehandhabt
        // und nicht mehr hier ausgeschlossen. Siehe: app/Config/FieldDisplayRules.php
    ];

    /**
     * Felder, die NUR ausgeblendet werden, wenn die Offerte NICHT gekauft wurde.
     * Nach dem Kauf werden diese Felder sichtbar (Kontaktdaten).
     */
    public array $excludedFieldsBeforePurchase = [
        // Persönliche Kontaktdaten
        'vorname',
        'firstname',
        'first_name',
        'nachname',
        'lastname',
        'last_name',
        'surname',
        'email',
        'e-mail',
        'e_mail',
        'email_address',
        'mail',
        'e-mail-adresse',
        'telefon',
        'telefonnummer',
        'phone',
        'telephone',
        'phone_number',
        'tel',
    ];
}
