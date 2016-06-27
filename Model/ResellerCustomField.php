<?php

namespace App\Plugins\Reseller\Model;

use Illuminate\Database\Eloquent\Model;

class ResellerCustomField extends Model
{
    protected $table = 'reseller_custom_fields';
    protected $fillable = ['custom_id', 'field_name', 'isrequired', 'fieldtype', 'department_id', 'title'];
}
