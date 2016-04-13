<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class EntityLike extends Model {
	
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'entity_like';
	
	public $timestamps = false;
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['entity_id'];
	
	public function user() {
		
		return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
		
	}
	
	public function entity() {
		
		return $this->belongsTo('ApartmentApi\Models\Entity','entity_id','id');
	}
}
