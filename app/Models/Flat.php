<?php
namespace ApartmentApi\Models;

use ApartmentApi\Models\BaseModel;

class Flat extends BaseModel
{
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'flat';

	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
        'flat_no',
        'type',
        'floor',
        'occupancy',
        'square_feet_1',
        'square_feet_2',
        'bill_to_name',
        'parking_slot_1',
        'parking_slot_2',
        'specific_amount',
        'contact_number',
        'block_id',
        'society_id'
    ];

	public function society()
    {
		return $this->belongsTo('ApartmentApi\Models\Society');
	}

	public function user()
    {
		return $this->belongsTo('ApartmentApi\Models\User');
	}

	public function block()
    {
		return $this->belongsTo('ApartmentApi\Models\Block');
	}

    public function flatDetails()
    {
        return $this->hasOne('ApartmentApi\Models\UserSociety', 'flat_id', 'id');
    }

    public function scopeSocietyId($query, $societyId)
    {
        return $query->whereSocietyId($societyId);
    }

    public function getFlatAndWingAttribute()
    {
        return $this->block->block . ' - ' . $this->attributes['flat_no'];
    }

    public function getJQuerySelect2Attribute()
    {
        return [
                'id' => $this->id,
                'text' => $this->attributes['flat_no'] . ' - ' .
                            ucfirst(@$this->userSociety->block->block) . ' - ' .
                            ucfirst(@$this->userSociety->building->name)
            ];
    }

    public function userSociety()
    {
        return $this->hasOne('ApartmentApi\Models\UserSociety');
    }

    public function scopeIsActive($query)
    {
        return $query->whereStatus(1);
    }

    public function flatBill()
    {
        return $this->hasOne('ApartmentApi\Models\FlatBill');
    }

}
