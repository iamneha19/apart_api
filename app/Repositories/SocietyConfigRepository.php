<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\Block;
use ApartmentApi\Models\Billing;
use ApartmentApi\Models\SocietyConfig;
use Repository\Repository;
use Illuminate\Support\Collection;
use ApartmentApi\Repositories\Traits\BillingItemRelationTrait;
use Carbon\Carbon;

 /**
  * Society Config Repository
  *
  * @author Mohammed Mudasir
  */
class SocietyConfigRepository extends Repository
{
    protected $model;

    protected $defaultSelection = ['id'];

    protected $fewSelection = [
        'id',
        'society_id',
        'building_count',
        'is_approved',
        'notes',
    ];

    protected $perPage = 1;

    protected $selection;

    public function __construct(SocietyConfig $model)
    {
        $this->model = $model;
    }

    public function find($societyId, $returnModel = true)
    {
        return $this->fewSelection()
                    ->model
                    ->whereSocietyId($societyId)
                    ->with([
                        'buildings.amenities' => function($q) {
                            $q->select('amenity.id', 'name as text');
                        }, 'buildings' => function($q) {
                            $q->select('id', 'parent_id', 'name', 'wing_exists');
                        },
                        'amenities' => function($q) {
                            $q->select('amenity.id', 'name as text');
                        }])
                    ->first();
    }
}
