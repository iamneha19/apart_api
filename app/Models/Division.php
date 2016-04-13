<?php

namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $table = 'division';

    protected $fillable = ['name','state_id'];

     public function state()
    {
        return $this->belongsTo('ApartmentApi\Models\State','id');
    }
}

