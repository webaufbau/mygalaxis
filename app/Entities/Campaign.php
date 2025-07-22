<?php

namespace App\Entities;

class Campaign extends Base
{
    protected $attributes = [
       'company_name' => '',
       'company_email' => '',
       'company_contact_person' => '',
       'company_address' => '',
       'company_zip' => '',
       'company_city' => '',
       'company_canton' => '',
       'company_phone' => '',
       'company_website' => '',
       'company_industry' => '',
       'company_categories' => '',
       'company_languages' => '',
       'company_notes' => '',

       'subject' => '',
       'message' => '',
       'status' => '',
       'sent_at' => '',
       'response_at' => '',

       'created_at' => '',
       'updated_at' => '',
    ];

    protected $dates = ['created_at'];
}
