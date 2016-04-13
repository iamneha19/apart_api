<?php namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class SocietyConfig extends Model
{
    protected $table = 'society_config';

    protected $fillable = [
        'society_id',
        'building_count',
        'is_approved',
        'wing_exists',
        'is_approved',
        'approved_by',
        'notes'
    ];

    public $timestamps = false;

    public function amenities()
    {
        return $this->morphToMany('ApartmentApi\Models\Amenity', 'taggable', 'amenity_tags');
    }

    public function buildings()
    {
        return $this->hasMany('ApartmentApi\Models\Society', 'parent_id', 'society_id');
    }
}
