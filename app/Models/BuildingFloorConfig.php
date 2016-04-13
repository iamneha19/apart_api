<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingFloorConfig extends Model {

    protected $table = 'building_config_floor_info';
    
    protected $fillable = ['id','building_configuration_id','floor_no','no_of_flat'];
    
    public $timestamps = false;
    
    public function building()
    {
        return $this->belongsTo('ApartmentApi\Models\BuildingConfig', 'building_configuration_id');
    }

}
