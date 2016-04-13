<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\FlatBill;
use Repository\Repository;
use Illuminate\Support\Collection;
use Carbon\Carbon;

 /**
  * City Repository
  *
  * @author Mohammed Mudasir
  */
class FlatBillRepository extends Repository
{
    protected $model;

    protected $defaultSelection = ['id'];

    protected $fewSelection = ['id', 'society_id', 'flat_id', 'charge', 'priority', 'month', 'status'];

    protected $perPage = 5;

    protected $selection;

    public function __construct(FlatBill $model)
    {
        $this->model = $model;
    }

    public function syncFlatBill($societyId, $flatId, $month)
    {
        return $this->model->firstOrCreate([
            'society_id' => $societyId,
            'flat_id' => $flatId,
            'month'   => $month
        ]);
    }

    public function hasSociety($societyId)
    {
        $this->model = $this->model->whereSocietyId($societyId);

        return $this;
    }

    public function detachBills($societyId, $month)
    {
        $flatBills = $this->model
                    ->where([
                        'society_id' => $societyId,
                        'month' => $month,
                    ])
                    ->whereStatus('unpaid')
                    ->delete();
                    // ->get()->toArray();

        return $flatBills;
    }

    public function checkStatus($status)
    {
        $this->model = $this->model->where(function($q) use ($status) {
            if ($status) {
                $q->whereStatus(ucfirst($status));
            }
        });

        return $this;
    }
    
    public function getReceiptSpecificDetails() {
        return $this->model->with([
            'flat.flatDetails.society' => function($q) {
                $q->select('id', 'parent_id', 'name');
            }, 
            
            'flat.flatDetails.building' => function($q) {
                $q->select('id', 'parent_id', 'name');
            }, 
                    
            'flat.flatDetails.block' => function($q) {
                $q->select('id', 'society_id', 'block');
            },
                    
            'flat.flatDetails.user' => function($q) {
                $q->select('id', 'first_name', 'last_name', 'email');
            }, 
                         
            'flat.flatDetails' => function($q) {
                $q->select('id', 'society_id', 'block_id', 'flat_id', 'user_id', 'building_id');
            },  
                    
            'flat' => function($q) {
                $q->select('id', 'flat_no');
            }, 
            ])->find($this->model->id);
    }

    public function getPaginatedList($societyId, Carbon $carbonDate, $status = '')
    {
        return $this->flatBillsQuery($societyId, $carbonDate, $status)
                    ->paginate(false);
    }

    public function flatBillsQuery(
                    $societyId,
                    Carbon $carbonDate,
                    $status = null,
                    $loadSociety = false,
                    $loadUser = false)
    {
        $self = $this->fewSelection()
                ->handleWithMethod('flatBillItems', function($q) use ($carbonDate) {
                    $q->where('month', $carbonDate->format('F Y'))
                      ->with(['item' => function($q) use ($carbonDate) {
                          $q->select('id', 'name', 'charge', 'fixed_billing_item', 'month')
                            ->where(function($q) use ($carbonDate) {
                                $q->orWhere('fixed_billing_item', 'YES')
                                  ->orWhere('month', 'LIKE', $carbonDate->format('Y-%m-%'));
                            });
                      }]);
                })
                ->handleWithMethod('flat.block.building', ['id', 'parent_id', 'name'])
                ->handleWithMethod('flat.block', ['id', 'society_id', 'block'])
                ->withFlat(function($q) {
                    $q->isActive()
                      ->select('id', 'block_id', 'flat_no', 'square_feet_1', 'type', 'status');
                })
                ->where(['month' => $carbonDate->format('F Y')]);

        if ($loadSociety) {
            $self = $self->withSociety($loadSociety);
        }

        if ($loadUser) {
            $self = $self->handleWithMethod('flat.flatDetails.user', $loadUser);
        }

        return $self->checkStatus($status)
                ->hasSociety($societyId);
    }

}
