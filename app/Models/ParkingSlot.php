<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParkingSlot extends Model {
	
//	use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'parking_slot';
	
//	protected $primaryKey = 'meeting_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['slot_name','vehicle_type','category_id','society_id','parking_config_id','status'];
    
//    protected $dates = ['deleted_at'];
	
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
    
    
	
}
