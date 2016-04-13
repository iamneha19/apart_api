<?php

namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'region';

    protected $fillable = ['name','division_id','state_id'];

     public function division()
    {
        return $this->belongsTo('ApartmentApi\Models\Division','id');
    }
    
    public function state()
    {
        return $this->belongsTo('ApartmentApi\Models\State','id');
    }
}

