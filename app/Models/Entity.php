<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entity extends Model {
	
	
	use SoftDeletes;
	
    const ENTITY_TYPE_POST = 1;
	const ENTITY_TYPE_ALBUM = 2;
	const ENTITY_TYPE_GROUP = 3;
	const ENTITY_TYPE_IMAGE = 4;
	const ENTITY_TYPE_REPLY = 5;
	const ENTITY_TYPE_POLL = 6;
	const ENTITY_TYPE_NOTICE = 7;
	const Entity_TYPE_FOLDER = 8;
	const ENTITY_TYPE_DOCUMENT = 9;
	const ENTITY_TYPE_FORUM_TOPIC = 10;
	const ENTITY_TYPE_OFFICIAL_COMM_REPLY = 12;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'entity';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['title', 'text','entity_type_id','society_id', 'status','user_id'];
	
	/**
	 * Used for soft deleting
	 * @var unknown
	 */
	protected $dates = ['deleted_at'];
		
	public function children() {
		
		return $this->hasMany('ApartmentApi\Models\Entity','parent_id','id');
	}
	
	public function parent() {
		
		return $this->belongsTo('ApartmentApi\Models\Entity','parent_id','id');
	}
	
	public function user() {
		
		return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
	}
	
	/**
	 * Table inheritance
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function entitiable() {
		return $this->morphTo();
	}
        
        public function society() {
            return $this->belongsTo('ApartmentApi\Models\Society','society_id','id');
        }
}
