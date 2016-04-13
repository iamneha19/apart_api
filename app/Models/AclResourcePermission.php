<?php

namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class AclResourcePermission extends Model
{   
    public $table = 'acl_resource_permission';
    public $timestamps = false;
    
    public function resource(){
    	
        return $this->belongsTo('ApartmentApi\Models\AclResource','resource_acl_name','acl_name');
    }
}