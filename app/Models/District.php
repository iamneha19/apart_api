<?php

namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'district';

    protected $fillable = ['name','region_id','state_id','division_id'];

     public function region()
    {
        return $this->belongsTo('ApartmentApi\Models\Region','id');
    }
}

