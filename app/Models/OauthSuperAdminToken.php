<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

use ApartmentApi\Repositories\Contracts\OAuthContract;

class OauthSuperAdminToken extends Model implements OAuthContract
{
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'token';

	public $timestamps = false;

	protected $table = 'oauth_super_admin_token';

	protected $fillable = array('token','created');

	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	public function user() {

		return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
	}

	public function client() {

		return $this->belongsTo('ApartmentApi\Models\OauthClient','client_id','id');
	}

}
