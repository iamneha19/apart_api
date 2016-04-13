<?php namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class BillingConfig extends Model
{
    protected $table = 'billing_config';

    public $timestamps = false;

    protected $fillable = ['society_id', 'key', 'value'];
}
