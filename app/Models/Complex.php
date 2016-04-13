<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class Complex extends Model
{
	public $table = 'complex';

	public $timestamps = false;

	protected $fillable = ['state_id','city_id','pincode','nearest_station','landmark','address_line_2'];

	public function society() {

		return $this->morphOne('ApartmentApi\Models\Society', 'society');
	}

}
