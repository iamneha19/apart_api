<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Block extends Model
{
	//use SoftDeletes;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'block';

	//protected $primaryKey = 'id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['block','user_id','society_id'];

	/**
	 * Used for soft deleting
	 * @var unknown
	 */
//protected $dates = ['deleted_at'];

    public function flats()
    {
        return $this->hasMany('ApartmentApi\Models\Flat','block_id','id');
    }

    public function society()
    {
        return $this->belongsTo('ApartmentApi\Models\Society');
    }

    public function building()
    {
        return $this->belongsTo('ApartmentApi\Models\Society', 'society_id');
    }

    public function user()
    {
        return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
    }

    public function amenities()
    {
        return $this->morphToMany('ApartmentApi\Models\Amenity', 'taggable', 'amenity_tags');
    }

	public function getSocietyWithBlockAttribute()
    {
        return ucfirst($this->society->name) . ' - block: ' . ucfirst($this->block);
    }
}
