<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model {
	
	use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'file';
	
	//protected $primaryKey = 'meeting_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['folder_type','category_id','physical_path','http_path','name','user_id','visible_to','description','folder_id','status'];
	
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
        protected $dates = ['deleted_at'];
	public function user()
        {
          return $this->belongsTo('ApartmentApi\Models\User');
        }
        
        public function category()
        {
          return $this->belongsTo('ApartmentApi\Models\Category');
        }
        
        public function folder()
        {
            return $this->morphTo();
        }
}
