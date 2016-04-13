<?php

namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'city';

    protected $fillable = ['state_id', 'name'];

    public function state()
    {
        return $this->belongsTo('ApartmentApi\Models\State');
    }
}
