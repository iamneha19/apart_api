<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficialCommReply extends Model {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'official_communication_reply';

	protected $fillable = ['letter_id', 'user_id', 'society_id','comment'];

	public $timestamps = true;


	public function user() {
		return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
	}

	public function society() {
		return $this->belongsTo('ApartmentApi\Models\Society','society_id','id');
	}

	//For Letter
	public function officialcomm() {
		return $this->belongsTo('ApartmentApi\Models\OfficialComm','letter_id','id');
	}

}