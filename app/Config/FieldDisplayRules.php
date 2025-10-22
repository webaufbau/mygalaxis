<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Field Display Rules Configuration
 *
 * Definiert, wie Felder mit Bedingungen angezeigt werden sollen.
 * Diese Rules werden sowohl in Email-Templates als auch in Firmen-Ansichten verwendet.
 *
 * Struktur einer Conditional Group:
 * [
 *     'type' => 'conditional_group',
 *     'label' => 'Anzeige-Label',
 *     'conditions' => [
 *         [
 *             'when' => ['feld1' => 'Wert1', 'feld2' => 'Wert2'],
 *             'display' => 'Anzeige-Text mit {platzhaltern}',
 *             'image' => 'optional_bildurl' // optional
 *         ]
 *     ],
 *     'fields_to_hide' => ['feld1', 'feld2', 'feld3']
 * ]
 */
class FieldDisplayRules extends BaseConfig
{
    /**
     * Hole alle Display Rules
     */
    public function getRules(): array
    {
        return [
            // Gartenbau - Bodenplatten
            'bodenplatten_vorplatz_gruppe' => $this->bodenplattenVorplatzRegel(),
            'bodenplatten_haus_gruppe' => $this->bodenplattenHausRegel(),
            'bodenplatten_sitzplatz_gruppe' => $this->bodenplattenSitzplatzRegel(),
            'bodenplatten_gehweg_gruppe' => $this->bodenplattenGehwegRegel(),
            'bodenplatten_balkon_gruppe' => $this->bodenplattenBalkonRegel(),
            'bodenplatten_andere_gruppe' => $this->bodenplattenAndereRegel(),

            // Gartenbau - Kies
            'kies_vorplatz_gruppe' => $this->kiesVorplatzRegel(),
            'kies_haus_gruppe' => $this->kiesHausRegel(),
            'kies_sitzplatz_gruppe' => $this->kiesSitzplatzRegel(),
            'kies_gehweg_gruppe' => $this->kiesGehwegRegel(),
            'kies_vereinzelnd_gruppe' => $this->kiesVereinzelndRegel(),
            'kies_andere_gruppe' => $this->kiesAndereRegel(),

            // Gartenbau - Mauern
            'mauer_deko_gruppe' => $this->mauerDekoRegel(),
            'mauer_abstuetzung_gruppe' => $this->mauerAbstuetzungRegel(),
            'mauer_teich_gruppe' => $this->mauerTeichRegel(),
            'mauer_seite_gruppe' => $this->mauerSeiteRegel(),
            'mauer_hang_gruppe' => $this->mauerHangRegel(),
            'mauer_andere_gruppe' => $this->mauerAndereRegel(),

            // Gartenbau - Zaun
            'zaun_vor_haus_gruppe' => $this->zaunVorHausRegel(),
            'zaun_seite_haus_gruppe' => $this->zaunSeiteHausRegel(),
            'zaun_alle_haus_gruppe' => $this->zaunAlleHausRegel(),
            'zaun_andere_gruppe' => $this->zaunAndereRegel(),

            // Gartenbau - Dielen
            'diele_haus_gruppe' => $this->dieleHausRegel(),
            'diele_sitzplatz_gruppe' => $this->dieleSitzplatzRegel(),
            'diele_gehweg_gruppe' => $this->dieleGehwegRegel(),
            'diele_pool_gruppe' => $this->dielePoolRegel(),
            'diele_balkon_gruppe' => $this->dieleBalkonRegel(),
            'diele_andere_gruppe' => $this->dieleAndereRegel(),

            // Gartenbau - Teich
            'teich_reinigung_gruppe' => $this->teichReinigungRegel(),
            'teich_neu_gruppe' => $this->teichNeuRegel(),

            // Gartenbau - Pool
            'pool_form_gruppe' => $this->poolFormRegel(),

            // Gartenbau - Hecke
            'hecke_schneiden_gruppe' => $this->heckeSchneidenRegel(),
            'hecke_entfernen_gruppe' => $this->heckeEntfernenRegel(),
            'hecke_pflanzen_gruppe' => $this->heckePflanzenRegel(),

            // Gartenbau - Baum
            'baum_schneiden_gruppe' => $this->baumSchneidenRegel(),
            'baum_entfernen_gruppe' => $this->baumEntfernenRegel(),
            'baum_pflanzen_gruppe' => $this->baumPflanzenRegel(),

            // Gartenbau - Rasen
            'rasen_maehen_gruppe' => $this->rasenMaehenRegel(),
            'rasen_ersetzen_gruppe' => $this->rasenErsetzenRegel(),
            'rasen_rollrasen_gruppe' => $this->rasenRollrasenRegel(),
            'rasen_sprinkler_gruppe' => $this->rasenSprinklerRegel(),
        ];
    }

    // ============================================================
    // Bodenplatten Rules
    // ============================================================

    private function bodenplattenVorplatzRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Bodenplatten: Vorplatz / Garage',
            'conditions' => [
                [
                    'when' => ['bodenplatten_vorplatz' => 'Ja', 'bodenplatten_vorplatz_flaeche' => 'Ja'],
                    'display' => '{bodenplatten_vorplatz_flaeche_ja} m²',
                ],
                [
                    'when' => ['bodenplatten_vorplatz' => 'Ja', 'bodenplatten_vorplatz_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['bodenplatten_vorplatz', 'bodenplatten_vorplatz_flaeche', 'bodenplatten_vorplatz_flaeche_ja'],
        ];
    }

    private function bodenplattenHausRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Bodenplatten: Ums Haus',
            'conditions' => [
                [
                    'when' => ['bodenplatten_haus' => 'Ja', 'bodenplatten_haus_flaeche' => 'Ja'],
                    'display' => '{bodenplatten_haus_flaeche_ja} m²',
                ],
                [
                    'when' => ['bodenplatten_haus' => 'Ja', 'bodenplatten_haus_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['bodenplatten_haus', 'bodenplatten_haus_flaeche', 'bodenplatten_haus_flaeche_ja'],
        ];
    }

    private function bodenplattenSitzplatzRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Bodenplatten: Sitzplatz',
            'conditions' => [
                [
                    'when' => ['bodenplatten_sitzplatz' => 'Ja', 'bodenplatten_sitzplatz_flaeche' => 'Ja'],
                    'display' => '{bodenplatten_sitzplatz_flaeche_ja} m²',
                ],
                [
                    'when' => ['bodenplatten_sitzplatz' => 'Ja', 'bodenplatten_sitzplatz_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['bodenplatten_sitzplatz', 'bodenplatten_sitzplatz_flaeche', 'bodenplatten_sitzplatz_flaeche_ja'],
        ];
    }

    private function bodenplattenGehwegRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Bodenplatten: Gehweg',
            'conditions' => [
                [
                    'when' => ['bodenplatten_gehweg' => 'Ja', 'bodenplatten_gehweg_flaeche' => 'Ja'],
                    'display' => '{bodenplatten_gehweg_flaeche_ja} m²',
                ],
                [
                    'when' => ['bodenplatten_gehweg' => 'Ja', 'bodenplatten_gehweg_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['bodenplatten_gehweg', 'bodenplatten_gehweg_flaeche', 'bodenplatten_gehweg_flaeche_ja'],
        ];
    }

    private function bodenplattenBalkonRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Bodenplatten: Balkon',
            'conditions' => [
                [
                    'when' => ['bodenplatten_balkon' => 'Ja', 'bodenplatten_balkon_flaeche' => 'Ja'],
                    'display' => '{bodenplatten_balkon_flaeche_ja} m²',
                ],
                [
                    'when' => ['bodenplatten_balkon' => 'Ja', 'bodenplatten_balkon_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['bodenplatten_balkon', 'bodenplatten_balkon_flaeche', 'bodenplatten_balkon_flaeche_ja'],
        ];
    }

    private function bodenplattenAndereRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Bodenplatten: Andere',
            'conditions' => [
                [
                    'when' => ['bodenplatten_andere' => 'Ja', 'bodenplatten_andere_flaeche' => 'Ja'],
                    'display' => '{bodenplatten_andere_flaeche_ja} m²',
                ],
                [
                    'when' => ['bodenplatten_andere' => 'Ja', 'bodenplatten_andere_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['bodenplatten_andere', 'bodenplatten_andere_flaeche', 'bodenplatten_andere_flaeche_ja'],
        ];
    }

    // ============================================================
    // Kies Rules
    // ============================================================

    private function kiesVorplatzRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Kies: Vorplatz / Garage',
            'conditions' => [
                [
                    'when' => ['kies_vorplatz' => 'Ja', 'kies_vorplatz_flaeche' => 'Ja'],
                    'display' => '{kies_vorplatz_flaeche_ja} m²',
                ],
                [
                    'when' => ['kies_vorplatz' => 'Ja', 'kies_vorplatz_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['kies_vorplatz', 'kies_vorplatz_flaeche', 'kies_vorplatz_flaeche_ja'],
        ];
    }

    private function kiesHausRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Kies: Ums Haus',
            'conditions' => [
                [
                    'when' => ['kies_haus' => 'Ja', 'kies_haus_flaeche' => 'Ja'],
                    'display' => '{kies_haus_flaeche_ja} m²',
                ],
                [
                    'when' => ['kies_haus' => 'Ja', 'kies_haus_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['kies_haus', 'kies_haus_flaeche', 'kies_haus_flaeche_ja'],
        ];
    }

    private function kiesSitzplatzRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Kies: Sitzplatz',
            'conditions' => [
                [
                    'when' => ['kies_sitzplatz' => 'Ja', 'kies_sitzplatz_flaeche' => 'Ja'],
                    'display' => '{kies_sitzplatz_flaeche_ja} m²',
                ],
                [
                    'when' => ['kies_sitzplatz' => 'Ja', 'kies_sitzplatz_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['kies_sitzplatz', 'kies_sitzplatz_flaeche', 'kies_sitzplatz_flaeche_ja'],
        ];
    }

    private function kiesGehwegRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Kies: Gehweg',
            'conditions' => [
                [
                    'when' => ['kies_gehweg' => 'Ja', 'kies_gehweg_flaeche' => 'Ja'],
                    'display' => '{kies_gehweg_flaeche_ja} m²',
                ],
                [
                    'when' => ['kies_gehweg' => 'Ja', 'kies_gehweg_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['kies_gehweg', 'kies_gehweg_flaeche', 'kies_gehweg_flaeche_ja'],
        ];
    }

    private function kiesVereinzelndRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Kies: Vereinzelnd',
            'conditions' => [
                [
                    'when' => ['kies_vereinzelnd' => 'Ja', 'kies_vereinzelnd_flaeche' => 'Ja'],
                    'display' => '{kies_vereinzelnd_flaeche_ja} m²',
                ],
                [
                    'when' => ['kies_vereinzelnd' => 'Ja', 'kies_vereinzelnd_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['kies_vereinzelnd', 'kies_vereinzelnd_flaeche', 'kies_vereinzelnd_flaeche_ja'],
        ];
    }

    private function kiesAndereRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Kies: Andere',
            'conditions' => [
                [
                    'when' => ['kies_andere' => 'Ja', 'kies_andere_flaeche' => 'Ja'],
                    'display' => '{kies_andere_flaeche_ja} m²',
                ],
                [
                    'when' => ['kies_andere' => 'Ja', 'kies_andere_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['kies_andere', 'kies_andere_flaeche', 'kies_andere_flaeche_ja'],
        ];
    }

    // ============================================================
    // Mauer Rules (Masse statt Fläche)
    // ============================================================

    private function mauerDekoRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Mauer: Deko',
            'conditions' => [
                [
                    'when' => ['mauer_deko' => 'Ja'],
                    'display' => '{mauer_deko_masse}',
                ],
            ],
            'fields_to_hide' => ['mauer_deko', 'mauer_deko_masse'],
        ];
    }

    private function mauerAbstuetzungRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Mauer: Abstützung',
            'conditions' => [
                [
                    'when' => ['mauer_abstuetzung' => 'Ja'],
                    'display' => '{mauer_abstuetzung_masse}',
                ],
            ],
            'fields_to_hide' => ['mauer_abstuetzung', 'mauer_abstuetzung_masse'],
        ];
    }

    private function mauerTeichRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Mauer: Teich',
            'conditions' => [
                [
                    'when' => ['mauer_teich' => 'Ja'],
                    'display' => '{mauer_teich_masse}',
                ],
            ],
            'fields_to_hide' => ['mauer_teich', 'mauer_teich_masse'],
        ];
    }

    private function mauerSeiteRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Mauer: Seite',
            'conditions' => [
                [
                    'when' => ['mauer_seite' => 'Ja'],
                    'display' => '{mauer_seite_masse}',
                ],
            ],
            'fields_to_hide' => ['mauer_seite', 'mauer_seite_masse'],
        ];
    }

    private function mauerHangRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Mauer: Hang',
            'conditions' => [
                [
                    'when' => ['mauer_hang' => 'Ja'],
                    'display' => '{mauer_hang_masse}',
                ],
            ],
            'fields_to_hide' => ['mauer_hang', 'mauer_hang_masse'],
        ];
    }

    private function mauerAndereRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Mauer: Andere',
            'conditions' => [
                [
                    'when' => ['mauer_andere' => 'Ja'],
                    'display' => '{mauer_andere_masse}',
                ],
            ],
            'fields_to_hide' => ['mauer_andere', 'mauer_andere_masse'],
        ];
    }

    // ============================================================
    // Zaun Rules
    // ============================================================

    private function zaunVorHausRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Zaun: Vor dem Haus',
            'conditions' => [
                [
                    'when' => ['zaun_vor_haus' => 'Ja'],
                    'display' => '{zaun_vor_haus_masse}',
                ],
            ],
            'fields_to_hide' => ['zaun_vor_haus', 'zaun_vor_haus_masse'],
        ];
    }

    private function zaunSeiteHausRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Zaun: Seitlich des Hauses',
            'conditions' => [
                [
                    'when' => ['zaun_seite_haus' => 'Ja'],
                    'display' => '{zaun_seite_haus_masse}',
                ],
            ],
            'fields_to_hide' => ['zaun_seite_haus', 'zaun_seite_haus_masse'],
        ];
    }

    private function zaunAlleHausRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Zaun: Alle Seiten',
            'conditions' => [
                [
                    'when' => ['zaun_alle_haus' => 'Ja'],
                    'display' => '{zaun_alle_haus_masse}',
                ],
            ],
            'fields_to_hide' => ['zaun_alle_haus', 'zaun_alle_haus_masse'],
        ];
    }

    private function zaunAndereRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Zaun: Andere',
            'conditions' => [
                [
                    'when' => ['zaun_andere' => 'Ja'],
                    'display' => '{zaun_andere_masse}',
                ],
            ],
            'fields_to_hide' => ['zaun_andere', 'zaun_andere_masse'],
        ];
    }

    // ============================================================
    // Dielen Rules
    // ============================================================

    private function dieleHausRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Dielen: Ums Haus',
            'conditions' => [
                [
                    'when' => ['diele_haus' => 'Ja', 'diele_haus_flaeche' => 'Ja'],
                    'display' => '{diele_haus_flaeche_ja} m²',
                ],
                [
                    'when' => ['diele_haus' => 'Ja', 'diele_haus_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['diele_haus', 'diele_haus_flaeche', 'diele_haus_flaeche_ja'],
        ];
    }

    private function dieleSitzplatzRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Dielen: Sitzplatz',
            'conditions' => [
                [
                    'when' => ['diele_sitzplatz' => 'Ja', 'diele_sitzplatz_flaeche' => 'Ja'],
                    'display' => '{diele_sitzplatz_flaeche_ja} m²',
                ],
                [
                    'when' => ['diele_sitzplatz' => 'Ja', 'diele_sitz platz_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['diele_sitzplatz', 'diele_sitzplatz_flaeche', 'diele_sitzplatz_flaeche_ja'],
        ];
    }

    private function dieleGehwegRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Dielen: Gehweg',
            'conditions' => [
                [
                    'when' => ['diele_gehweg' => 'Ja', 'diele_gehweg_flaeche' => 'Ja'],
                    'display' => '{diele_gehweg_flaeche_ja} m²',
                ],
                [
                    'when' => ['diele_gehweg' => 'Ja', 'diele_gehweg_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['diele_gehweg', 'diele_gehweg_flaeche', 'diele_gehweg_flaeche_ja'],
        ];
    }

    private function dielePoolRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Dielen: Pool',
            'conditions' => [
                [
                    'when' => ['diele_pool' => 'Ja', 'diele_pool_flaeche' => 'Ja'],
                    'display' => '{diele_pool_flaeche_ja} m²',
                ],
                [
                    'when' => ['diele_pool' => 'Ja', 'diele_pool_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['diele_pool', 'diele_pool_flaeche', 'diele_pool_flaeche_ja'],
        ];
    }

    private function dieleBalkonRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Dielen: Balkon',
            'conditions' => [
                [
                    'when' => ['diele_balkon' => 'Ja', 'diele_balkon_flaeche' => 'Ja'],
                    'display' => '{diele_balkon_flaeche_ja} m²',
                ],
                [
                    'when' => ['diele_balkon' => 'Ja', 'diele_balkon_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['diele_balkon', 'diele_balkon_flaeche', 'diele_balkon_flaeche_ja'],
        ];
    }

    private function dieleAndereRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Dielen: Andere',
            'conditions' => [
                [
                    'when' => ['diele_andere' => 'Ja', 'diele_andere_flaeche' => 'Ja'],
                    'display' => '{diele_andere_flaeche_ja} m²',
                ],
                [
                    'when' => ['diele_andere' => 'Ja', 'diele_andere_flaeche' => 'Nein'],
                    'display' => 'Fläche unbekannt',
                ],
            ],
            'fields_to_hide' => ['diele_andere', 'diele_andere_flaeche', 'diele_andere_flaeche_ja'],
        ];
    }

    // ============================================================
    // Teich Rules
    // ============================================================

    private function teichReinigungRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Teich: Reinigung',
            'conditions' => [
                [
                    'when' => ['teich_reinigung' => 'Ja'],
                    'display' => '{teich_reinigung_flaeche} m²',
                ],
            ],
            'fields_to_hide' => ['teich_reinigung', 'teich_reinigung_flaeche'],
        ];
    }

    private function teichNeuRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Teich: Neu anlegen',
            'conditions' => [
                [
                    'when' => ['teich_neu' => 'Ja'],
                    'display' => '{teich_neu_flaeche} m²',
                ],
            ],
            'fields_to_hide' => ['teich_neu', 'teich_neu_flaeche'],
        ];
    }

    // ============================================================
    // Pool Rules
    // ============================================================

    private function poolFormRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Pool',
            'conditions' => [
                [
                    'when' => ['pool_form' => 'Ja'],
                    'display' => '{pool_form_groesse}',
                ],
            ],
            'fields_to_hide' => ['pool_form', 'pool_form_groesse'],
        ];
    }

    // ============================================================
    // Hecke Rules
    // ============================================================

    private function heckeSchneidenRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Hecke: Schneiden',
            'conditions' => [
                [
                    'when' => ['hecke_schneiden' => 'Ja'],
                    'display' => '{hecke_schneiden_masse}',
                ],
            ],
            'fields_to_hide' => ['hecke_schneiden', 'hecke_schneiden_masse'],
        ];
    }

    private function heckeEntfernenRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Hecke: Entfernen',
            'conditions' => [
                [
                    'when' => ['hecke_entfernen' => 'Ja'],
                    'display' => '{hecke_entfernen_masse}',
                ],
            ],
            'fields_to_hide' => ['hecke_entfernen', 'hecke_entfernen_masse'],
        ];
    }

    private function heckePflanzenRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Hecke: Pflanzen',
            'conditions' => [
                [
                    'when' => ['hecke_pflanzen' => 'Ja'],
                    'display' => '{hecke_pflanzen_masse}',
                ],
            ],
            'fields_to_hide' => ['hecke_pflanzen', 'hecke_pflanzen_masse'],
        ];
    }

    // ============================================================
    // Baum Rules
    // ============================================================

    private function baumSchneidenRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Baum: Schneiden',
            'conditions' => [
                [
                    'when' => ['baum_schneiden' => 'Ja'],
                    'display' => '{baum_schneiden_masse}',
                ],
            ],
            'fields_to_hide' => ['baum_schneiden', 'baum_schneiden_masse'],
        ];
    }

    private function baumEntfernenRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Baum: Entfernen',
            'conditions' => [
                [
                    'when' => ['baum_entfernen' => 'Ja'],
                    'display' => '{baum_entfernen_baumart} ({baum_entfernen_anzahl} Stück)',
                ],
            ],
            'fields_to_hide' => ['baum_entfernen', 'baum_entfernen_baumart', 'baum_entfernen_anzahl'],
        ];
    }

    private function baumPflanzenRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Baum: Pflanzen',
            'conditions' => [
                [
                    'when' => ['baum_pflanzen' => 'Ja'],
                    'display' => '{baum_pflanzen_baumart} ({baum_pflanzen_anzahl} Stück)',
                ],
            ],
            'fields_to_hide' => ['baum_pflanzen', 'baum_pflanzen_baumart', 'baum_pflanzen_anzahl'],
        ];
    }

    // ============================================================
    // Rasen Rules
    // ============================================================

    private function rasenMaehenRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Rasen: Mähen',
            'conditions' => [
                [
                    'when' => ['rasen_maehen' => 'Ja'],
                    'display' => '{rasen_maehen_flaeche} m²',
                ],
            ],
            'fields_to_hide' => ['rasen_maehen', 'rasen_maehen_flaeche'],
        ];
    }

    private function rasenErsetzenRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Rasen: Ersetzen',
            'conditions' => [
                [
                    'when' => ['rasen_ersetzen' => 'Ja'],
                    'display' => '{rasen_ersetzen_masse}',
                ],
            ],
            'fields_to_hide' => ['rasen_ersetzen', 'rasen_ersetzen_masse'],
        ];
    }

    private function rasenRollrasenRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Rasen: Rollrasen',
            'conditions' => [
                [
                    'when' => ['rasen_rollrasen' => 'Ja'],
                    'display' => '{rasen_rollrasen_flaeche} m²',
                ],
            ],
            'fields_to_hide' => ['rasen_rollrasen', 'rasen_rollrasen_flaeche'],
        ];
    }

    private function rasenSprinklerRegel(): array
    {
        return [
            'type' => 'conditional_group',
            'label' => 'Rasen: Sprinkler',
            'conditions' => [
                [
                    'when' => ['rasen_sprinkler' => 'Ja'],
                    'display' => '{rasen_sprinkler_masse}',
                ],
            ],
            'fields_to_hide' => ['rasen_sprinkler', 'rasen_sprinkler_masse'],
        ];
    }
}
