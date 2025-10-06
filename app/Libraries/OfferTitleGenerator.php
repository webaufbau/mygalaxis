<?php

namespace App\Libraries;

class OfferTitleGenerator
{
    /**
     * Generiert einen aussagekräftigen Titel für ein Angebot
     */
    public function generateTitle(array $offer): string
    {
        $type = $offer['type'] ?? 'unknown';
        $translatedType = lang('Offers.type.' . $type);
        $city = ucwords($offer['city'] ?? '');

        // Fallback wenn keine Stadt
        if (empty($city)) {
            return $translatedType;
        }

        switch ($type) {
            case 'move':
                return $this->generateMoveTitle($offer, $translatedType, $city);

            case 'move_cleaning':
                return $this->generateMoveCleaningTitle($offer, $translatedType, $city);

            case 'cleaning':
                return $this->generateCleaningTitle($offer, $translatedType, $city);

            case 'painting':
                return $this->generatePaintingTitle($offer, $translatedType, $city);

            case 'gardening':
                return $this->generateGardeningTitle($offer, $translatedType, $city);

            case 'electrician':
                return $this->generateElectricianTitle($offer, $translatedType, $city);

            case 'plumbing':
                return $this->generatePlumbingTitle($offer, $translatedType, $city);

            case 'heating':
                return $this->generateHeatingTitle($offer, $translatedType, $city);

            case 'flooring':
                return $this->generateFlooringTitle($offer, $translatedType, $city);

            case 'tiling':
                return $this->generateTilingTitle($offer, $translatedType, $city);

            default:
                return "{$translatedType} in {$city}";
        }
    }

    protected function generateMoveTitle(array $offer, string $type, string $city): string
    {
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);

        $from = $this->ensureString($formFields['von_ort'] ?? $city);
        $to = $this->ensureString($formFields['nach_ort'] ?? '');
        $rooms = $formFields['auszug_zimmer'] ?? '';

        // "Umzug von Basel 3 Zi nach Zürich"
        $title = $type;

        if ($from) {
            $title .= " von " . ucwords($from);
            if ($rooms) {
                $roomText = $this->formatRoomSize($rooms);
                if ($roomText) {
                    $title .= " {$roomText}";
                }
            }
        }

        if ($to && $to !== $from) {
            $title .= " nach " . ucwords($to);
        } elseif (!$from) {
            $title .= " in {$city}";
        }

        return $title;
    }

    protected function generateMoveCleaningTitle(array $offer, string $type, string $city): string
    {
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);
        $formFieldsCombo = json_decode($offer['form_fields_combo'] ?? '{}', true);

        $from = $this->ensureString($formFields['von_ort'] ?? $formFieldsCombo['von_ort'] ?? $city);
        $to = $this->ensureString($formFields['nach_ort'] ?? $formFieldsCombo['nach_ort'] ?? '');
        $rooms = $formFields['auszug_zimmer'] ?? $formFieldsCombo['auszug_zimmer'] ?? '';

        $title = $type;

        if ($from) {
            $title .= " von " . ucwords($from);
            if ($rooms) {
                $roomText = $this->formatRoomSize($rooms);
                if ($roomText) {
                    $title .= " {$roomText}";
                }
            }
        }

        if ($to && $to !== $from) {
            $title .= " nach " . ucwords($to);
        } elseif (!$from) {
            $title .= " in {$city}";
        }

        return $title;
    }

    protected function generateCleaningTitle(array $offer, string $type, string $city): string
    {
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);

        $cleaningType = $formFields['reinigungsart'] ?? '';
        $rooms = $formFields['wohnung_groesse'] ?? $formFields['komplett_anzahlzimmer'] ?? '';

        // Sicherstellen dass cleaningType ein String ist
        if (is_array($cleaningType)) {
            $cleaningType = $cleaningType[0] ?? '';
        }

        $title = $type . " in {$city}";

        if ($rooms) {
            $roomText = $this->formatRoomSize($rooms);
            if ($roomText) {
                $title .= " - {$roomText}";
            }
        }

        if ($cleaningType && $cleaningType !== 'Standard') {
            $title .= " ({$cleaningType})";
        }

        return $title;
    }

    protected function generatePaintingTitle(array $offer, string $type, string $city): string
    {
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);

        $objectType = $this->ensureString($formFields['art_objekt'] ?? '');
        $works = $formFields['arbeiten_wohnung'] ?? $formFields['malerarbeiten_uebersicht'] ?? [];

        $title = $type . " in {$city}";

        if ($objectType) {
            $title .= " - {$objectType}";
        }

        if (!empty($works) && is_array($works)) {
            $workText = implode(', ', array_slice($works, 0, 2));
            $title .= " ({$workText})";
        }

        return $title;
    }

    protected function generateGardeningTitle(array $offer, string $type, string $city): string
    {
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);

        $works = $formFields['garten_anlegen'] ?? [];

        $title = $type . " in {$city}";

        if (!empty($works) && is_array($works)) {
            $workText = implode(', ', array_slice($works, 0, 2));
            $title .= " - {$workText}";
        }

        return $title;
    }

    protected function generateElectricianTitle(array $offer, string $type, string $city): string
    {
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);

        $objectType = $this->ensureString($formFields['art_objekt'] ?? '');
        $works = $formFields['arbeiten_elektriker'] ?? [];

        $title = $type . " in {$city}";

        if ($objectType) {
            $title .= " - {$objectType}";
        }

        if (!empty($works) && is_array($works)) {
            $workText = implode(', ', array_slice($works, 0, 2));
            $title .= " ({$workText})";
        }

        return $title;
    }

    protected function generatePlumbingTitle(array $offer, string $type, string $city): string
    {
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);

        $objectType = $this->ensureString($formFields['art_objekt'] ?? '');
        $works = $formFields['arbeiten_sanitaer'] ?? [];

        $title = $type . " in {$city}";

        if ($objectType) {
            $title .= " - {$objectType}";
        }

        if (!empty($works) && is_array($works)) {
            $workText = implode(', ', array_slice($works, 0, 2));
            $title .= " ({$workText})";
        }

        return $title;
    }

    protected function generateHeatingTitle(array $offer, string $type, string $city): string
    {
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);

        $objectType = $this->ensureString($formFields['art_objekt'] ?? '');
        $works = $formFields['arbeiten_heizung'] ?? [];

        $title = $type . " in {$city}";

        if ($objectType) {
            $title .= " - {$objectType}";
        }

        if (!empty($works) && is_array($works)) {
            $workText = implode(', ', array_slice($works, 0, 2));
            $title .= " ({$workText})";
        }

        return $title;
    }

    protected function generateFlooringTitle(array $offer, string $type, string $city): string
    {
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);

        $objectType = $this->ensureString($formFields['art_objekt'] ?? '');
        $works = $formFields['arbeiten_boden'] ?? [];

        $title = $type . " in {$city}";

        if ($objectType) {
            $title .= " - {$objectType}";
        }

        if (!empty($works) && is_array($works)) {
            $workText = implode(', ', array_slice($works, 0, 2));
            $title .= " ({$workText})";
        }

        return $title;
    }

    protected function generateTilingTitle(array $offer, string $type, string $city): string
    {
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);

        $objectType = $this->ensureString($formFields['art_objekt'] ?? '');
        $works = $formFields['arbeiten_platten'] ?? [];

        $title = $type . " in {$city}";

        if ($objectType) {
            $title .= " - {$objectType}";
        }

        if (!empty($works) && is_array($works)) {
            $workText = implode(', ', array_slice($works, 0, 2));
            $title .= " ({$workText})";
        }

        return $title;
    }

    /**
     * Stellt sicher, dass ein Wert ein String ist (behandelt Arrays)
     */
    protected function ensureString($value): string
    {
        if (is_array($value)) {
            return $value[0] ?? '';
        }
        return (string)$value;
    }

    /**
     * Formatiert Zimmer-Angabe (1 → 1 Zi, 2 → 2 Zi, etc.)
     */
    protected function formatRoomSize($rooms): string
    {
        if (empty($rooms)) {
            return '';
        }

        // Wenn es ein Array ist, ersten Wert nehmen
        if (is_array($rooms)) {
            $rooms = $rooms[0] ?? '';
            if (empty($rooms)) {
                return '';
            }
        }

        // Sicherstellen dass es ein String ist
        $rooms = (string)$rooms;

        // Wenn es bereits "Zimmer" enthält, direkt zurückgeben
        if (strpos(strtolower($rooms), 'zimmer') !== false || strpos(strtolower($rooms), 'zi') !== false) {
            return $rooms;
        }

        // Wenn es eine Zahl ist
        if (is_numeric($rooms)) {
            return $rooms . ' Zi';
        }

        // Wenn Format "1-Zimmer", "2-Zimmer"
        if (preg_match('/^(\d+)-?Zimmer/i', $rooms, $matches)) {
            return $matches[1] . ' Zi';
        }

        // Andere Fälle
        if ($rooms === 'Andere' || strtolower($rooms) === 'andere') {
            return 'EFH/Andere';
        }

        return $rooms;
    }
}
