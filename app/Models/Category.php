<?php 

namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = "category";
    protected $primaryKey = 'id';
    protected $fillable = ['name','description','type','society_id','is_mandatory'];
    public $timestamps = false;

    public function types() 
    {
        return $this->hasMany('ApartmentApi\Models\Society','society_category_id');
    }
   
    
    public function reminder() 
    {
        return $this->hasOne('\ApartmentApi\Models\Reminders', 'type_id');
        
    }
}

