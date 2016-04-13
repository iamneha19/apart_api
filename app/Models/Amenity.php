<?php namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model {

	protected $table = 'amenity';
    
    protected $fillable = ['id', 'name'];
	
	public $timestamps = false;


    public function amenities()
    {
        return $this->morphToMany('ApartmentApi\Models\Amenity', 'taggable', 'amenity_tags');
    }
	
	 public function subAmenities()
    {
        return $this->hasMany('ApartmentApi\Models\SubAmenities');
    }
}
