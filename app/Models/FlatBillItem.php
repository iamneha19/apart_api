<?php

namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class FlatBillItem extends Model
{
    protected $table = 'flat_billing_item';

    protected $fillable = ['id', 'society_id', 'flat_id', 'item_id', 'month'];

    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo('ApartmentApi\Models\BillingItem');
    }

    public function flat()
    {
        return $this->belongsTo('ApartmentApi\Models\Flat');
    }

    public function flatBill()
    {
        return $this->hasOne('ApartmentApi\Models\FlatBill', 'flat_id', 'flat_id');
    }
}
