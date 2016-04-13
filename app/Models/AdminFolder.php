<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminFolder extends Model {
	
	use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'admin_folder';
	
	//protected $primaryKey = 'meeting_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name','user_id','flat_id','type','society_id','status'];
	
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
        protected $dates = ['deleted_at'];
        public function user()
        {
          return $this->belongsTo('ApartmentApi\Models\User');
        }
        
        public function files()
        {
            return $this->morphMany('ApartmentApi\Models\File', 'folder');
        }
	
}
