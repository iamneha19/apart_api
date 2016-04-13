<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class SocietyRoleResourcePermission extends Model
{
	
	public $table = 'society_role_resource_permission';
	public $timestamps = false;
        
    protected $fillable = ['society_role_id','resource_permission_id'];
	
	
	
	public function aclresourcepermission() {
		
		return $this->belongsTo('ApartmentApi\Models\AclResourcePermission','resource_permission_id','id');
	}
        
    public function societyrole() {
		
		return $this->belongsTo('ApartmentApi\Models\SocietyRole','society_role_id','id');
	}
	
	
	
}