<?php
namespace ApartmentApi\Models;

use ApartmentApi\Models\BaseModel;

class Building extends BaseModel
{
	public $table = 'building';

    public $timestamps = false;

    protected $fillable = ['id', 'floors','flats','blocks'];

	public function society() {

		return $this->morphOne('ApartmentApi\Models\Society', 'society');
	}

}
