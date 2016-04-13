<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class AclRole extends Model
{

	protected $table = "acl_role";

        protected $primaryKey = 'id';

//         public $incrementing = false;

//        protected $fillable = ['acl_name'];

	public $timestamps = false;

//        public function aclUserRole() {
//            return $this->hasMany('ApartmentApi\Models\AclUserRole','role_acl_name');
//        }

	public function resources() {

        return $this->hasMany('ApartmentApi\Models\AclRoleResource','resource');
	}

        public function society() {

		return $this->belongsTo('ApartmentApi\Models\Society','society_id');
	}
	
	public function aclUserRole() {

		return $this->hasOne('ApartmentApi\Models\AclUserRole','acl_role_id');
	}
}
