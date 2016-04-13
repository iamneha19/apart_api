<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApartmentUser extends Model {
	
	use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'apartment_user';
	
	protected $primaryKey = 'apartment_user_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id','owner','commitee_member','admin_member','designation','responsibility','description','address','city_id','pincode','active_status','status'];
	
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
        protected $dates = ['deleted_at'];
	
}
