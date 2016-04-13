<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class UserSociety extends Model
{

	protected $table = "user_society";

    protected $fillable = [
        'society_id',
        'building_id',
        'block_id',
        'flat_id',
        'user_id',
        'relation',
        'status',
        'commitee_member'
    ];

	public $timestamps = false;

	public function user() {

		return $this->belongsTo('ApartmentApi\Models\User','user_id');
	}

	public function society() {

		return $this->belongsTo('ApartmentApi\Models\Society','society_id');
	}

	public  function block() {

		return $this->belongsTo('ApartmentApi\Models\Block','block_id');
	}

	public function flat() {

		return $this->belongsTo('ApartmentApi\Models\Flat','flat_id');
	}

	public function building() {
		return $this->belongsTo('ApartmentApi\Models\Society', 'building_id', 'id');
	}

}
