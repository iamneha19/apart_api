<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\City;
use Repository\Repository;
use Illuminate\Support\Collection;

 /**
  * City Repository
  *
  * @author Mohammed Mudasir
  */
class CityRepository extends Repository
{
    protected $model;

    protected $defaultSelection = ['id'];

    protected $fewSelection = ['state_id', 'name'];

    protected $perPage = 5;

    public function __construct(City $model)
    {
        $this->model = $model;
    }

    public function stateId($id)
    {
        $this->model = $this->model->whereStateId($id);

        return $this;
    }

    public function findWithState()
    {
        return call_user_func_array([$this->withState(), 'find'], func_get_args());
    }

    public function withState()
    {
        $this->model = $this->model->with(['state' => function($q)
        {
            $q->select('id', 'name');
        }]);

        return $this;
    }

    public function search(Collection $queries)
    {
        $repo = $this->withState();

        $repo = $queries->get('orderby') ? $repo->orderBy('name', 'ASC') : $repo;

        $repo = $queries->get('state_id') ? $repo->stateId($queries->get('state_id')): $repo;

        if ($searchContent = $queries->get('search'))
        {
            $repo = $this->whereLike('name', $searchContent);
        }

        $this->model = $this->model->groupBy('name');

        return $queries->get('per_page') === 'unlimited' ? $repo->get()->toArray() : $repo->paginate();
    }

}
