<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class TaskCategory extends Model {
	
	//use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'task_category';
	
	//protected $primaryKey = 'meeting_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['category_name','society_id','created_by','active_status','status'];
	
	public function user() {
		return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
	}
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
       // protected $dates = ['deleted_at'];
	
}
