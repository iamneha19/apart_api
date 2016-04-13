<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model {
	

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'event';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'date'];
	
	public $timestamps = false;
	
	
	public function user() {
	
		return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
	}
	
	public function society() {
		return $this->belongsTo('ApartmentApi\Models\Society','society_id','id');
	}
	
}