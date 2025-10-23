<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Offer Subtypes Configuration
 *
 * Definiert die Zuordnung von Subtypes zu ihren Hauptkategorien (offer types).
 * Wird verwendet für:
 * - E-Mail Template Auswahl
 * - Admin Dropdown für Subtypes
 * - Offer Type Detection
 */
class OfferSubtypes extends BaseConfig
{
    /**
     * Mapping von Subtype zu Type (Branche)
     *
     * Format: 'subtype_name' => 'offer_type'
     *
     * @var array<string, string>
     */
    public array $subtypeToTypeMapping = [
        // Umzug
        'umzug_privat'  => 'move',
        'umzug_firma'   => 'move',

        // Reinigung
        'reinigung_wohnung'       => 'cleaning',
        'reinigung_haus'          => 'cleaning',
        'reinigung_gewerbe'       => 'cleaning',
        'reinigung_andere'        => 'cleaning',
        'reinigung_nur_fenster'   => 'cleaning',
        'reinigung_fassaden'      => 'cleaning',
        'reinigung_hauswartung'   => 'cleaning',

        // Maler
        'maler_wohnung' => 'painting',
        'maler_haus'    => 'painting',
        'maler_gewerbe' => 'painting',
        'maler_andere'  => 'painting',

        // Garten
        'garten_neue_gartenanlage'        => 'gardening',
        'garten_garten_umgestalten'       => 'gardening',
        'garten_allgemeine_gartenpflege'  => 'gardening',
        'garten_andere_gartenarbeiten'    => 'gardening',

        // Einzelne Gewerke (keine Subtypes - direktes Mapping)
        'elektriker'   => 'electrician',
        'sanitaer'     => 'plumbing',
        'heizung'      => 'heating',
        'plattenleger' => 'tiling',
        'bodenleger'   => 'flooring',
    ];

    /**
     * Hole alle Subtypes für einen bestimmten Offer Type
     *
     * @param string $offerType
     * @return array Liste von Subtypes
     */
    public function getSubtypesForType(string $offerType): array
    {
        $subtypes = [];

        foreach ($this->subtypeToTypeMapping as $subtype => $type) {
            if ($type === $offerType) {
                $subtypes[] = $subtype;
            }
        }

        return $subtypes;
    }

    /**
     * Hole den Offer Type für einen Subtype
     *
     * @param string $subtype
     * @return string|null
     */
    public function getTypeForSubtype(string $subtype): ?string
    {
        return $this->subtypeToTypeMapping[$subtype] ?? null;
    }

    /**
     * Prüfe ob ein Offer Type Subtypes hat
     *
     * @param string $offerType
     * @return bool
     */
    public function hasSubtypes(string $offerType): bool
    {
        return count($this->getSubtypesForType($offerType)) > 0;
    }

    /**
     * Hole menschenlesbare Labels für Subtypes
     *
     * @return array<string, string>
     */
    public function getSubtypeLabels(): array
    {
        return [
            // Umzug
            'umzug_privat'  => 'Umzug Privat',
            'umzug_firma'   => 'Umzug Firma',

            // Reinigung
            'reinigung_wohnung'       => 'Reinigung Wohnung',
            'reinigung_haus'          => 'Reinigung Haus',
            'reinigung_gewerbe'       => 'Reinigung Gewerbe',
            'reinigung_andere'        => 'Reinigung Andere',
            'reinigung_nur_fenster'   => 'Reinigung nur Fenster',
            'reinigung_fassaden'      => 'Reinigung Fassaden',
            'reinigung_hauswartung'   => 'Reinigung Hauswartung',

            // Maler
            'maler_wohnung' => 'Maler Wohnung',
            'maler_haus'    => 'Maler Haus',
            'maler_gewerbe' => 'Maler Gewerbe',
            'maler_andere'  => 'Maler Andere',

            // Garten
            'garten_neue_gartenanlage'        => 'Garten Neue Gartenanlage',
            'garten_garten_umgestalten'       => 'Garten umgestalten',
            'garten_allgemeine_gartenpflege'  => 'Allgemeine Gartenpflege',
            'garten_andere_gartenarbeiten'    => 'Andere Gartenarbeiten',

            // Einzelne Gewerke
            'elektriker'   => 'Elektriker',
            'sanitaer'     => 'Sanitär',
            'heizung'      => 'Heizung',
            'plattenleger' => 'Plattenleger',
            'bodenleger'   => 'Bodenleger',
        ];
    }
}
