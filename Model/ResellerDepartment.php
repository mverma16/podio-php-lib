<?php namespace App\Plugins\Reseller\Model;

use Illuminate\Database\Eloquent\Model;

class ResellerDepartment extends Model {

    protected $table = 'reseller_department';
    protected $fillable = ['middledpt_id','rcdpt_name'];

}
