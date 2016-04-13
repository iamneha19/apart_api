<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class AclResource extends Model
{  
    public $incrementing = false;
    protected $primaryKey = "acl_name";
    public $table = 'acl_resource';
    public $timestamps = false;
         
     public function permissions(){
        return $this->hasMany('ApartmentApi\Models\AclResourcePermission','resource_acl_name');
}
}