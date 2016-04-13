<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\Block;
use ApartmentApi\Models\Billing;
use Repository\Repository;
use Illuminate\Support\Collection;
use ApartmentApi\Repositories\Traits\BillingItemRelationTrait;
use Carbon\Carbon;

 /**
  * City Repository
  *
  * @author Mohammed Mudasir
  */
class BillingRepository extends Repository
{
    use BillingItemRelationTrait;

    protected $model;

    protected $defaultSelection = ['id'];

    protected $fewSelection = ['id', 'society_id', 'office_charge', 'residential_charge', 'shop_charge', 'month'];

    protected $perPage = 5;

    protected $selection;

    public function __construct(Billing $model)
    {
        $this->model = $model;

        $this->setDefaultRelationshipSelection();
    }

    private function setDefaultRelationshipSelection()
    {
        $this->selection = [
            'society' => function ($q) {
                $q->select('society.id');
            },
            'buildings' => function ($q) {
                $q->select('society.id', 'society.parent_id', 'society.name');
            },
            'blocks' => function ($q) {
                $q->select('block.id', 'block.society_id', 'block.block');
            },
            'flats' => function ($q) {
                $q->isActive()
                  ->select('flat.id', 'flat.block_id', 'flat.flat_no', 'flat.type')
                  ->with(['flatBill' => function($q) {
                      $q->select('id', 'flat_id', 'status');
                  }]);
            }
        ];
    }

    public function setRelationshipSelection($selectionName, array $selectionValue)
    {
        $this->selection[$selectionName] = function($q) use ($selectionValue) {
            call_user_func_array([$q, 'select'], $selectionValue);
        };

        return $this;
    }

    public function withSocietyFlats()
    {
        $this->model = $this->model->with([
                'society.buildings.blocks.flats' => $this->selection['flats'],
                'society.buildings.blocks' => $this->selection['blocks'],
                'society.buildings' => $this->selection['buildings'],
                'society' => $this->selection['society'],
            ]);

        return $this;
    }

    public function withUnpaidFlatBill()
    {
        $this->model = $this->model->with(['society.buildings.blocks.flats.flatBill' => function($q) {
            $q->select('id', 'flat_id', 'status')->whereStatus('unpaid');
        }]);

        return $this;
    }

    public function hasFlats(array $flatId = [])
    {
        return $this->model->newQuery()->whereHas('flats', function ($q) use ($flatId) {
            $q->whereFlatId($flatId);
        });
    }

    public function hasBuildings(array $buildingId = [])
    {
        return $this->model->newQuery()->whereHas('buildings', function ($q) use ($buildingId) {
            $q->whereBuildingId($buildingId);
        });
    }

    public function monthIs(Carbon $date)
    {
        $this->model = $this->model->monthIs($date);

        return $this;
    }
}
