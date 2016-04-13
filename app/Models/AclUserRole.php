<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class AclUserRole extends Model
{
	
	protected $table = "acl_user_role";
        
//        protected $fillable = ['status'];
	
	public $timestamps = false;
	
	public function user() {
	
		return $this->belongsTo('ApartmentApi\Models\User','user_id');
	}
        
        public function aclRole() {
	
		return $this->belongsTo('ApartmentApi\Models\AclRole','acl_role_id');
	}
        
//        public function society() {
//		
//		return $this->belongsTo('ApartmentApi\Models\Society','society_id');
//	}
	
}