<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class AclUserResource extends Model
{
	
	public $table = 'acl_user_resource';
	public $timestamps = false;
        
        protected $fillable = ['acl_resource','society_id'];
	
	public function user() {

		return $this->belongsTo('ApartmentApi\Models\User','user_id');
	}
	
	public function resource() {
		
		return $this->belongsTo('ApartmentApi\Models\AclResource','acl_resource','acl_name');
	}
	
	public function society() {
	
		return $this->belongsTo('ApartmentApi\Models\Society','society_id');
	}
	
}