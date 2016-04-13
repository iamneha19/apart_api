<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class AclResourceRole extends Model
{
	
	protected $table = "acl_resource_role";
        
        protected $fillable = ['resource_acl_name'];
	
	public $timestamps = false;
	
	public function user() {
	
		return $this->belongsTo('ApartmentApi\Models\User','user_id');
	}
	
}