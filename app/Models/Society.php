<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class Society extends Model
{
	protected $table = 'society';

//	public $timestamps = false;

	protected $fillable = [
        'parent_id',
        'name',
        'address',
        'pincode',
        'city_id',
        'society_category_id',
        'street',
        'landmark',
        'nearest_station',
        'wing_exists'
    ];

	public function flats() {
		return $this->hasMany('ApartmentApi\Models\Flat');
	}

	public function users() {
		return $this->belongsToMany('ApartmentApi\Models\SocietyUser','society_user','society_id','user_id');
	}

    public function userSociety()
    {
        return $this->hasMany('ApartmentApi\Models\UserSociety', 'society_id', 'parent_id');
    }

    /**
     * @{{Deprecated}}
     * Building method is more flexible and carry better relationship aproch
     *
     * @return $this
     */
	public function parent() {

		return $this->belongsTo('ApartmentApi\Models\Society','parent_id');
	}

    /**
     * If current instance is building then it must return society
     * Note: Alternate aproch is to call parent method but it is Deprecated
     *
     * @return [type] [description]
     */
    public function society() {
       return $this->belongsTo('ApartmentApi\Models\Society','parent_id');
    }

    /**
     * Building Relationship in same table as parent id is society id
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function buildings()
    {
		return $this->hasMany('ApartmentApi\Models\Society','parent_id');
    }

    public function blocks()
    {
        return $this->hasMany('ApartmentApi\Models\Block');
    }

    public function addressDetail()
    {
        return $this->belongsTo('ApartmentApi\Models\AddressDetail');
    }

    public function getJQuerySelect2Attribute()
    {
        return [
            'id' => $this->id,
            'text' => $this->name,
        ];
    }

    public function firstOrNewBuilding($societyId, $id)
    {
        $building = $this->where([
                'id' => $id,
                'parent_id' => $societyId,
            ])->first();

        return $building ? $building : new self([
            'parent_id' => $societyId
        ]);
    }

    public function amenities()
    {
        return $this->morphToMany('ApartmentApi\Models\Amenity', 'taggable', 'amenity_tags');
    }
}
