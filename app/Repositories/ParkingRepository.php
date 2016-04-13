<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\FlatParking;
use ApartmentApi\Models\ParkingSlot;
use Illuminate\Support\Collection;
use Repository\Repository;

 /**
  * State Repository
  *
  * @author Mohammed Mudasir
  */
 class ParkingRepository extends Repository
 {
     protected $model;

     protected $defaultSelection = ['id'];

     protected $fewSelection = ['name'];

     protected $perPage = 5;

     public function __construct(ParkingSlot $model)
     {
         $this->model = $model;
     }
 }
