<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\FlatParking;
use Illuminate\Support\Collection;
use Repository\Repository;

 /**
  * State Repository
  *
  * @author Mohammed Mudasir
  */
 class FlatParkingRepository extends Repository
 {
     protected $model;

     protected $defaultSelection = ['id'];

     protected $fewSelection = ['name'];

     protected $perPage = 5;

     public function __construct(FlatParking $model)
     {
         $this->model = $model;
     }

    public function searchFlatParking(Collection $queries)
    {
        $this->model = $this->model->with(['parking'=> function($q) use ($queries) {
                            $q->select('id', 'slot_name', 'vehicle_type')
                                ->where('slot_name', 'LIKE', '%' . $queries->get('search') . '%')
                                ->orWhere('vehicle_type', 'LIKE', '%' . $queries->get('search') . '%');
                        }])
                        ->where('flat_id',$queries->get('id'));
                        
//        $repo = $this->whereLike('vehicle_type', $queries->get('search'));
//        print_r($repo);exit;
        
        return $this->paginate($queries->get('per_page'));
    }
 }
