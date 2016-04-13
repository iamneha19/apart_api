<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingConfig extends Model {

	protected $table = 'building_configuration';
        
        protected $fillable = ['id','society_id','no_of_floor','is_flat_same_on_each_floor','flat_on_each_floor'];
        public $timestamps = false;
       

}
