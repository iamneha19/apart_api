<?php

namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class AclUserResourcePermission extends Model {
    
    protected $table = "acl_user_resource_permission";
    public $timestamps = false;
    
    public function permission() {
	
        return $this->belongsTo('ApartmentApi\Models\AclResourcePermission','resource_permission_id');
	}
        
    public function user() {
	
	return $this->belongsTo('ApartmentApi\Models\User','user_id');
	}
        
        public function society() {
	
		return $this->belongsTo('ApartmentApi\Models\Society','society_id');
	}
}