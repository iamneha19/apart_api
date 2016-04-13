<?php namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use ApartmentApi\Models\Traits\BillingModuleRelationsTrait;

class BillingItem extends Model
{
    use BillingModuleRelationsTrait;

    protected $table = 'item';

    protected $fillable = [
        'society_id', 
        'name', 
        'flat_category', 
        'flat_type', 
        'charge', 
        'fixed_billing_item', 
        'month'
    ];

    public function scopeIsFixedItem($q)
    {
        return $q->where('fixed_billing_item', 'YES');
    }

    public function scopeOrIsFixedItem($q)
    {
        return $q->orWhere('fixed_billing_item', 'YES');
    }
}
