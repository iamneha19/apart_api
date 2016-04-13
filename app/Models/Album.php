<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Album extends Model {
	
	use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'album';
	
	//protected $primaryKey = 'meeting_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name','description','user_id','society_id','status'];
	
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
