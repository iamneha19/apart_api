<?php namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class SubAmenities extends Model {

	protected $table = 'sub_amenities';
    
    protected $fillable = ['id', 'name', 'amenity_id', 'society_id'];
	
    public $timestamps = false;


    

}
