<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Base extends Entity {

    public function getLink() {
        return site_url($this->getTitleField() . "/" . $this->getPrimaryKeyValue());
    }

    public function getUpdatedAt(string $format = 'd.m.Y H:i')
    {
        if (!isset($this->attributes['updated_at']) || is_null($this->attributes['updated_at'])) {
            return date($format);
        }

        // Check for invalid date
        if ($this->attributes['updated_at'] === '0000-00-00 00:00:00' || $this->attributes['updated_at'] === '-0001-11-30 00:00:00') {
            return date($format);
        }

        try {
            // Convert to CodeIgniter\I18n\Time object
            $this->attributes['updated_at'] = $this->mutateDate($this->attributes['updated_at']);

            $timezone = $this->timezone ?? app_timezone();

            $this->attributes['updated_at']->setTimezone($timezone);

            return $this->attributes['updated_at']->format($format);
        } catch (\Exception $e) {
            return date($format);
        }
    }

    public function getCreatedAt(string $format = 'd.m.Y H:i')
    {
        if (!isset($this->attributes['created_at']) || is_null($this->attributes['created_at'])) {
            return date($format);
        }

        // Check for invalid date
        if ($this->attributes['created_at'] === '0000-00-00 00:00:00' || $this->attributes['created_at'] === '-0001-11-30 00:00:00') {
            return date($format);
        }

        try {
            // Convert to CodeIgniter\I18n\Time object
            $this->attributes['created_at'] = $this->mutateDate($this->attributes['created_at']);

            $timezone = $this->timezone ?? app_timezone();

            $this->attributes['created_at']->setTimezone($timezone);

            return $this->attributes['created_at']->format($format);
        } catch (\Exception $e) {
            return date($format);
        }
    }


    public function getRowClass() {
        return '';
    }

    public function hasAttribute($attribute = null) {
        if(isset($this->attributes[$attribute])) {
            return true;
        }
        return false;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function setAttribute($attribute, $value=null) {
        if(array_key_exists($attribute, $this->attributes)) {
            if(is_array($value)) {
                $value = json_encode($value);
            }
            $this->attributes[$attribute] = $value;
        }
    }

    public function getValue($attribute = null) {
        if(array_key_exists($attribute, $this->attributes)) {
            return $this->attributes[$attribute];
        }
        return false;
    }

    public function getFields() {
        $attributes = $this->getAttributes();
        $values = [];
        foreach ($attributes as $column => $valueArr) {
            $prop = $column;
            $values[$prop] = $attributes[$prop];
        }
        return $values;
    }

    public function getModelShortname(): string
    {
        return strtolower(preg_replace('#App\\\\Entities\\\\([A-Za-z]*)#', '$1', get_class($this)));
    }

    public function getTableRowAttributes() {
        return ['class' => 'table_data_row_tr'];
    }

    public function getTimestamp($field) {
        return strtotime($this->$field);
    }

    public function getPrimaryKeyValue($primary_key_field_name=false) {
        if(!$primary_key_field_name) {
            foreach ($this->attributes as $key => $value) {
                if (str_ends_with($key, '_id')) {
                    $primary_key_field_name = $key;
                    break;
                }
            }
        }
        return $this->$primary_key_field_name;
    }

    public function getIdParams() {
        $params = [];

        foreach ($this->attributes as $key => $value) {
            if (substr($key, -3) === '_id') {
                $params[] = $key . '=' . $value;
            }
        }

        return '&' . implode('&', $params);
    }

    public function getTitleField() {
        if(property_exists($this, 'titleField')) {
            return $this->titleField;
        }
        foreach($this->getAttributes() as $field_name) {
            if(str_contains("_name", $field_name)) {
                return $field_name;
            }
            if(str_contains("_title", $field_name)) {
                return $field_name;
            }
        }
    }

    public function getTitle() {
        return $this->getValue($this->getTitleField());
    }

    public function getRawContent() {
        $attributes = $this->getAttributes();
        return implode(" ", $attributes);
    }

    public function getDate() {
        return $this->attributes['created_at'];
    }

    public function getImage() {
        return base_url('assets/images/placeholder.png');
    }

    public function getThumbImage() {
        return base_url('assets/images/placeholder.png');
    }

    public function getAuthor() {
        return 'EA Autor';
    }

    public function getTags() {
        return [];
    }

    public function getLocation() {
        return '';
    }

    public function getCategory() {
        return '';
    }

}
