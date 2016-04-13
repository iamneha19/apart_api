<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class SocietyRole extends Model
{
	
	protected $table = "society_role";
        
    protected $primaryKey = 'id';
            
    protected $fillable = ['role_title'];

	public function resources() {
	
        return $this->hasMany('ApartmentApi\Models\SocietyRoleResource','resource');
	}
   
}