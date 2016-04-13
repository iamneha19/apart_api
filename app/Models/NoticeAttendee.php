<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NoticeAttendee extends Model {

//	use SoftDeletes;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'notice_attendee';

	//protected $primaryKey = 'notice_id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['notice_id','role_id','active_status','status'];

	/**
	 * Used for soft deleting
	 * @var unknown
	 */
    public function entity() {
		return $this->belongsTo('ApartmentApi\Models\entity','notice_id','id');
	}

    public function role() {
		return $this->belongsTo('ApartmentApi\Models\AclRole','role_id','id');
	}
//        protected $dates = ['deleted_at'];

}
