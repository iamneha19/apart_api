<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class SocietyRoleResource extends Model
{
	
	public $table = 'society_role_resource';
	public $timestamps = false;
        
    protected $fillable = ['resource','society_role_id'];
	
	
	
	public function aclresource() {
		
		return $this->belongsTo('ApartmentApi\Models\AclResource','resource','acl_name');
	}
        
    public function societyrole() {
		
		return $this->belongsTo('ApartmentApi\Models\SocietyRole','society_role_id','id');
	}
	
	
	
}