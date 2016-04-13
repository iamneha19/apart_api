<?php namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class AddressDetail extends Model
{
    protected $table = 'address_detail';

    protected $fillable = ['street', 'landmark', 'nearest_station'];
}
