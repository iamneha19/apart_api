<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model {
	
	//use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'task';
	
	//protected $primaryKey = 'meeting_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['task_category_id','society_id','assign_to','title','created_by','type','recur_type','begin_on','due_on','active_status','status'];
	
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
       // protected $dates = ['deleted_at'];
	
	public function user() {
		return $this->belongsTo('ApartmentApi\Models\User','created_by','id');
	}
}
