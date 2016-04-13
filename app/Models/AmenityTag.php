<?php namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class AmenityTag extends Model {
	
	protected $table = 'amenity_tags';
    
    protected $fillable = ['id', 'society_id', 'amenity_id', 'sub_amenity_id', 'taggable_id', 'taggable_type'];
	
	public $timestamps = false;

	public function taggable() {
		return $this->morphTo();
	}
}
