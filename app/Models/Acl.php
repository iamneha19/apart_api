<?php
namespace ApartmentApi\Models\Acl;

use Illuminate\Database\Eloquent\Model;

class Acl extends Model
{
	
	function hasPermission($object,$permission) {
		
		return \DB::select('
				select id from user_acl 
				inner join acl on acl.id = user_acl.id where ');
	}
	
	function hasRole($role) {
		
	}
	
}