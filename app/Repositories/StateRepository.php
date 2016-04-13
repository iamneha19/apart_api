<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\State;
use Repository\Repository;
use Illuminate\Support\Collection;

 /**
  * State Repository
  *
  * @author Mohammed Mudasir
  */
 class StateRepository extends Repository
 {
     protected $model;

     protected $defaultSelection = ['id'];

     protected $fewSelection = ['name'];

     protected $perPage = 5;

     public function __construct(State $model)
     {
         $this->model = $model;
     }

     public function search(Collection $queries)
     {
         $repo = $queries->get('orderby') ? $this->orderBy('name', 'ASC') : $this;

         if ($searchContent = $queries->get('search'))
         {
             $repo = $this->whereLike('name', $searchContent);
         }

         $this->model = $this->model->groupBy('name');

         return $queries->get('per_page') === 'unlimited' ?
                         $repo->get() :
                         $repo->paginate();
     }
 }
