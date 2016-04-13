<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\Block;
use ApartmentApi\Models\Billing;
use ApartmentApi\Models\BillingItem;
use Repository\Repository;
use Illuminate\Support\Collection;
use ApartmentApi\Repositories\Traits\BillingItemRelationTrait;

 /**
  * Methods which are similer in billing and billing items
  *
  * @author Mohammed Mudasir
  */
class BillingItemRepository extends Repository
{
    use BillingItemRelationTrait;

    protected $model;

    protected $defaultSelection = ['id'];

    protected $fewSelection = ['id', 'name', 'flat_category', 'flat_type', 'charge', 'fixed_billing_item', 'month'];

    protected $perPage = 5;

    public function __construct(BillingItem $model)
    {
        $this->model = $model;
    }
}
