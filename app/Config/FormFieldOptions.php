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

        // UTM Parameter
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',

        // Interne Steuerungsfelder
        'skip_kontakt',
        'skip_reinigung_umzug',

        // Gartenbau - Bodenplatten (Detail-Felder für interne Berechnung)
        'bodenplatten_vorplatz',
        'bodenplatten_vorplatz_flaeche',
        'bodenplatten_haus',
        'bodenplatten_haus_flaeche',
        'bodenplatten_sitzplatz',
        'bodenplatten_sitzplatz_flaeche',
        'bodenplatten_gehweg',
        'bodenplatten_gehweg_flaeche',
        'bodenplatten_balkon',
        'bodenplatten_balkon_flaeche',
        'bodenplatten_andere',
        'bodenplatten_andere_flaeche',
        'bodenplatten_typ_andere',

        // Gartenbau - Kies (Detail-Felder für interne Berechnung)
        'kies_vorplatz',
        'kies_vorplatz_flaeche',
        'kies_haus',
        'kies_haus_flaeche',
        'kies_sitzplatz',
        'kies_sitzplatz_flaeche',
        'kies_gehweg',
        'kies_gehweg_flaeche',
        'kies_vereinzelnd',
        'kies_vereinzelnd_flaeche',
        'kies_andere',
        'kies_andere_flaeche',
        'kies_typ_andere',

        // Gartenbau - Mauern (Detail-Felder für interne Berechnung)
        'mauer_deko',
        'mauer_deko_masse',
        'mauer_abstuetzung',
        'mauer_abstuetzung_masse',
        'mauer_teich',
        'mauer_teich_masse',
        'mauer_seite',
        'mauer_seite_masse',
        'mauer_hang',
        'mauer_hang_masse',
        'mauer_andere',
        'mauer_andere_masse',
        'mauer_typ_andere',

        // Gartenbau - Zaun (Detail-Felder für interne Berechnung)
        'zaun_vor_haus',
        'zaun_vor_haus_masse',
        'zaun_seite_haus',
        'zaun_seite_haus_masse',
        'zaun_alle_haus',
        'zaun_alle_haus_masse',
        'zaun_andere',

        // Gartenbau - Dielen (Detail-Felder für interne Berechnung)
        'diele_haus',
        'diele_haus_flaeche',
        'diele_sitzplatz',
        'diele_sitzplatz_flaeche',
        'diele_gehweg',
        'diele_gehweg_flaeche',
        'diele_pool',
        'diele_pool_flaeche',
        'diele_balkon',
        'diele_balkon_flaeche',
        'diele_andere',
        'diele_andere_flaeche',
        'diele_andere1',

        // Gartenbau - Teich (Detail-Felder für interne Berechnung)
        'teich_reinigung_flaeche',
        'teich_neu_flaeche',

        // Gartenbau - Pool (Detail-Felder für interne Berechnung)
        'pool_form_groesse',

        // Gartenbau - Hecke (Detail-Felder für interne Berechnung)
        'hecke_schneiden_masse',
        'hecke_entfernen_masse',
        'hecke_pflanzen_masse',

        // Gartenbau - Baum (Detail-Felder für interne Berechnung)
        'baum_schneiden_masse',
        'baum_entfernen_baumart',
        'baum_entfernen_anzahl',
        'baum_pflanzen_baumart',
        'baum_pflanzen_anzahl',

        // Gartenbau - Rasen (Detail-Felder für interne Berechnung)
        'rasen_maehen_flaeche',
        'rasen_ersetzen_masse',
        'rasen_rollrasen_flaeche',
        'rasen_sprinkler_masse',

        // Gartenbau - Typ-Felder (bereits bei den jeweiligen Kategorien, aber zur Sicherheit)
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
        'zaun_a_d',
        'zaun_e_h',
        'zaun_i_l',
        'zaun_m_p',
        'zaun_q_t',
        'diele_a_d',
        'diele_e_h',
        'diele_i_l',
        'diele_m_p',
        'teich_a_d',
        'teich_e_h',
        'teich_i_l',
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
