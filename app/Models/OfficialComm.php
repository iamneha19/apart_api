<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficialComm extends Model {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'official_communication';

	protected $fillable = ['recipient_id', 'created_by', 'society_id','subject','text','subject_reference','document_reference','is_read'];

	public $timestamps = true;


	public function user() {

		return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
	}

	public function society() {
		return $this->belongsTo('ApartmentApi\Models\Society','society_id','id');
	}

	//For Role
	public function role() {
		return $this->belongsTo('ApartmentApi\Models\AclRole','acl_role_id','id');
	}

}
