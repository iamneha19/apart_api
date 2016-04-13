<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\FlatBillItem;
use Repository\Repository;
use Illuminate\Support\Collection;
use Carbon\Carbon;

 /**
  * City Repository
  *
  * @author Mohammed Mudasir
  */
class FlatBillItemRepository extends Repository
{
    protected $model;

    protected $defaultSelection = ['id'];

    protected $perPage = 10;

    protected $selection;

    public function __construct(FlatBillItem $model)
    {
        $this->model = $model;
    }

    public function syncFlatItem($societyId, $flatId, $itemId, $month)
    {
        return $this->model->firstOrCreate([
                        'society_id' => (int) $societyId,
                        'flat_id' => (int) $flatId,
                        'item_id' => (int) $itemId,
                        'month'   => $month
                     ]);
    }

    public function detachBillItems($societyId, Carbon $date)
    {
         $flatBillItems = $this->model
                    ->whereSocietyId($societyId)
                    ->where('month', $date->format('F Y'))
                    // Getting unpaid flat bills and month
                    ->whereHas('flatBill', function ($q) use ($date) {
                        $q->whereStatus('unpaid')
                        ->where('month', $date->format('F Y'));
                    })
                    ->delete();
                    // ->get()->toArray();

                    // dd($flatBillItems);

        return $flatBillItems;
    }
}
