<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Amenities extends Model {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'amenities';

	protected $fillable = ['id', 'user_id', 'society_id', 'name', 'description', 'image', 'charges'];

	public $timestamps = true;


	public function user() {

		return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
	}

	public function society() {
		return $this->belongsTo('ApartmentApi\Models\Society','x','id');
	}

	//For Role
	public function role() {
		return $this->belongsTo('ApartmentApi\Models\AclRole','acl_role_id','id');
	}

}
