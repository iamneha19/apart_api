<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class AclRoleResource extends Model
{
	
	public $table = 'acl_role_resource';
	public $timestamps = false;
        
        protected $fillable = ['resource','acl_role_id'];
	
	
	
	public function aclresource() {
		
		return $this->belongsTo('ApartmentApi\Models\AclResource','resource','acl_name');
	}
        
        public function role() {
		
		return $this->belongsTo('ApartmentApi\Models\AclRole','acl_role_id','id');
	}
	
	
	
}