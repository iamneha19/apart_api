<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class FlatParking extends Model {
	
//	use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'flat_parking';
	
//	protected $primaryKey = 'meeting_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['parking_slot_id','flat_id'];
	
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
//        protected $dates = ['deleted_at'];
    
//    public function parking() {
//        return $this->hasOne('ApartmentApi\Models\FlatParking', 'parking_slot_id');
//    }
	
    public function parking() 
    {
        return $this->belongsTo('\ApartmentApi\Models\ParkingSlot', 'parking_slot_id');
        
    }
}
