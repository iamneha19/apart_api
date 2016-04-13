<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class AdminFile extends Model {
	
	//use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'admin_file';
	
	//protected $primaryKey = 'meeting_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['type','name','user_id','description','admin_folder_id','active_status','status'];
	
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
       // protected $dates = ['deleted_at'];
	public function user()
        {
          return $this->belongsTo('ApartmentApi\Models\User');
        }
}
