<?php

namespace ApartmentApi\Models\Traits;
use Carbon\Carbon;

/**
 * Billing Item Relations
 *
 * @author Mohammed Mudasir
 */
trait BillingModuleRelationsTrait
{
    public function getMonthAttribute()
    {
        return Carbon::parse($this->attributes['month'])->format('F Y');
    }

    public function flats()
    {
        return $this->morphToMany('ApartmentApi\Models\Flat', 'bill_category', 'billable_flat');
    }

    public function buildings()
    {
        return $this->morphToMany('ApartmentApi\Models\Society', 'bill_category', 'billable_building', null, 'building_id');
    }

    public function society()
    {
        return $this->belongsTo('ApartmentApi\Models\Society');
    }

    public function scopeMonthIs($q, Carbon $date)
    {
        return $q->where('month', $date->format('Y-m-d'));
    }

    public function withOnlyFlatsAndBuildingsId()
    {
        return $this->with(['flats' => function($q) {
                    $q->select('flat.id');
                }, 'buildings' => function($q) {
                    $q->select('society.id');
                }]);
    }
}
