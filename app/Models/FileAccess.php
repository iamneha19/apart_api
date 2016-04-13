<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileAccess extends Model {
	
//	use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'file_access';
	
//	protected $primaryKey = 'meeting_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['flat_id','role_id','status'];
	
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
	 
//	public function user() {
//		return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
//	}
//        protected $dates = ['deleted_at'];
	public function file() {
		return $this->belongsTo('ApartmentApi\Models\File','file_id','id');
	}
    
    public function role() {
		return $this->belongsTo('ApartmentApi\Models\AclRole','role_id','id');
	}
}
