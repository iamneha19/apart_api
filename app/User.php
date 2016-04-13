<?php namespace ApartmentApi;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
        'flat_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'commitee_member',
        'admin_user',
        'contact_no',
        'dob',
        'unique_id',
        'voter_id',
    ];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

    public function userSociety()
    {
        return $this->hasOne('ApartmentApi\Models\UserSociety');
    }

    public function userSocieties()
    {
        return $this->hasMany('ApartmentApi\Models\UserSociety');
    }

    public function roles()
    {
        return $this->belongsToMany('ApartmentApi\Models\AclRole', 'acl_user_role');
    }
}
