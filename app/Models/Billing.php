<?php namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use ApartmentApi\Models\Traits\BillingModuleRelationsTrait;

class Billing extends Model
{
    use BillingModuleRelationsTrait;

    protected $table = 'billing';

    protected $fillable = ['society_id', 'office_charge', 'residential_charge', 'shop_charge', 'month'];

    public function userSociety()
    {
        return $this->hasMany('ApartmentApi\Models\UserSociety', 'society_id', 'society_id');
    }
}
