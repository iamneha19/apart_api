<?php

namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class AclRoleResourcePermission extends Model {
    
    protected $table = "acl_role_resource_permission";
    
    public $timestamps = false;
    
    
    public function permission() {
        return $this->belongsTo('ApartmentApi\Models\AclResourcePermission','resource_permission_id');
	}
	
    public function role() {
        return $this->belongsTo('ApartmentApi\Models\AclRole','acl_role_id');
    }
}
