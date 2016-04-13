<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingAttendee extends Model {
	
//	use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'meeting_attendee';
	
	//protected $primaryKey = 'meeting_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['meeting_id','role_id','active_status','status'];
	
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
    public function meeting() {
		return $this->belongsTo('ApartmentApi\Models\Meeting','meeting_id','id');
	}
    
    public function role() {
		return $this->belongsTo('ApartmentApi\Models\AclRole','role_id','id');
	}
//        protected $dates = ['deleted_at'];
	
}
