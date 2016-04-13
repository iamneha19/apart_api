<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class ParkingConfig extends Model {
	
	//use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'parking_config';
	
//	protected $primaryKey = 'meeting_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['category_id','stack_row','stack_column','total_slot','slot_charges','slot_name_prefix','society_id', 'name', 'styling'];
	
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
        protected $dates = ['deleted_at'];
	
}
