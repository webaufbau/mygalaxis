<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Entities;

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Authentication\Traits\HasAccessTokens;
use CodeIgniter\Shield\Authentication\Traits\HasHmacTokens;
use CodeIgniter\Shield\Authorization\Traits\Authorizable;
use CodeIgniter\Shield\Entities\UserIdentity;
use CodeIgniter\Shield\Models\LoginModel;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Traits\Activatable;
use CodeIgniter\Shield\Traits\Bannable;
use CodeIgniter\Shield\Traits\Resettable;

class User extends \CodeIgniter\Shield\Entities\User
{
    use Authorizable;
    use HasAccessTokens;
    use HasHmacTokens;
    use Resettable;
    use Activatable;
    use Bannable;

    /**
     * @var UserIdentity[]|null
     */
    private ?array $identities = null;

    private ?string $email         = null;
    private ?string $password      = null;
    private ?string $password_hash = null;
    private ?string $active = null;


    private ?string $company_name = null;
    private ?string $contact_person = null;
    private ?string $company_uid = null;
    private ?string $company_street = null;
    private ?string $company_zip = null;
    private ?string $company_city = null;
    private ?string $company_website = null;
    private ?string $company_email = null;
    private ?string $company_phone = null;
    private ?string $filter_address = null;
    private ?string $filter_cantons = null;
    private ?string $filter_languages = null;
    private ?string $filter_absences = null;
    private ?string $account_balance = null;
    private ?string $auto_purchase = null;
    private ?string $email_text = null;
    private ?string $stripe_customer_id = null;

    /**
     * @var string[]
     * @phpstan-var list<string>
     * @psalm-var list<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'last_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'          => '?integer',
        'active'      => 'int_bool',
        'permissions' => 'array',
        'groups'      => 'array',
    ];

    protected $titleField = "company_name";

    public function getValue($attribute = null) {
        if(property_exists($this, $attribute)) {
            return $this->$attribute;
        }
        return false;
    }

    public function hasAttribute($attribute = null) {
        if(isset($this->attributes[$attribute])) {
            return true;
        }
        return false;
    }

    public function getAttribute($attribute = null): string {
        if(isset($this->attributes[$attribute])) {
            return $this->attributes[$attribute];
        }
        return '';
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function setAttribute($attribute, $value=null): void {
        if(array_key_exists($attribute, $this->getAttributes())) {
            if(is_array($value)) {
                $value = json_encode($value);
            }
            $this->attributes[$attribute] = $value;
        }
    }

    public function getPhoto(): ?string
    {
        return $this->getAttributes()['photo'];
    }

    public function getRowClass() {
        return '';
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

    public function getRawContent() {
        $attributes = $this->getAttributes();
        $content = implode(" ", $attributes);
        return $content;
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

    public function getDate() {
        return $this->attributes['created_at'];
    }

    public function getImage() {
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
