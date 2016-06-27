<?php namespace App\Plugins\Reseller\Model;

use Illuminate\Database\Eloquent\Model;

class Reseller extends Model {

	protected $table='reseller';

	protected $fillable = ['userid','apikey','url'];
        
         public function setUrlAttribute($value){
            $this->attributes['url']=str_finish($value, '/');
        }

}
