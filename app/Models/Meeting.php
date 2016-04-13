<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model {
	
	use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'meeting';
	
//	protected $primaryKey = 'meeting_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['title','date','venue','agenda','attendees','description','society_id','society_id','building_id','user_id','type_id','day','week','hour','active_status','status'];
	
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
     protected $dates = ['deleted_at'];
	 
	public function user() {
		return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
	}
    
    public function society() {
		return $this->belongsTo('ApartmentApi\Models\Society','society_id','id');
	}
	
}
