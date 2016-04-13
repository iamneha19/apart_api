<?php namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    public function scopeIsActive($query)
    {
        return $query->whereActiveStatus('S');
    }
}
