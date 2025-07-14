<?php namespace App\Entities;

use CodeIgniter\Entity;
use CodeIgniter\I18n\Time;

class AuthIdentity extends Base {
    protected $attributes = [
        'id' => '',
        'user_id' => '',
        'type' => '',
        'name' => '',
        'secret' => '',
        'secret2' => '',
        'expires' => '',
        'extra' => '',
        'force_reset' => '',
        'last_used_at' => ''
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

}
