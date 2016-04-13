<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'notice';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['id','type', 'expiry_date','replyto_email'];
	
	public $timestamps = false;
	
	public function entity() {
		
		return $this->morphOne('ApartmentApi\Models\Entity', 'entitiable');
		
	}
	
}