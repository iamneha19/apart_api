<?php namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class FlatBill extends Model
{
    protected $table = 'flat_bill';

    protected $fillable = ['bill_id', 'society_id', 'flat_id', 'priority', 'month', 'charge', 'status'];

    public function flat()
    {
        return $this->belongsTo('ApartmentApi\Models\Flat');
    }

    public function flatBillItems()
    {
        return $this->hasMany('ApartmentApi\Models\FlatBillItem', 'flat_id', 'flat_id');
    }

    public function getStatusAttribute($value)
    {
        return ucfirst($value);
    }

    public function payment()
    {
        return $this->hasOne('ApartmentApi\Models\Payment');
    }

    public function society()
    {
        return $this->belongsTo('ApartmentApi\Models\Society');
    }
}
