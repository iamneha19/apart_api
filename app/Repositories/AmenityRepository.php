<?php

namespace ApartmentApi\Repositories;

use Repository\Repository;
use Illuminate\Support\Collection;
use ApartmentApi\Repositories\Traits\BillingItemRelationTrait;
use Carbon\Carbon;
use ApartmentApi\Models\Amenity;


 /**
  * City Repository
  *
  * @author Swapnil Chaudhari
  */
class AmenityRepository extends Repository
{
    use BillingItemRelationTrait;

    protected $model;

    protected $defaultSelection = ['id'];

    protected $fewSelection = ['id', 'name'];

//    protected $perPage = 5;

    protected $selection;

    public function __construct(Amenity $model)
    {
        $this->model = $model;

    }


		
}
