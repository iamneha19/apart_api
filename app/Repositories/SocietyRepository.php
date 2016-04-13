<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\Society;
use Repository\Repository;
use Illuminate\Support\Collection;
use Repository\Selector;

 /**
  * City Repository
  *
  * @author Mohammed Mudasir
  */
class SocietyRepository extends Repository
{
    protected $model;

    protected $defaultSelection = ['id'];

    protected $fewSelection = ['id', 'name'];

    protected $perPage = 5;

    public function __construct(Society $model)
    {
        $this->model = $model;
    }

    public function billSpecificBuildingsBlocksFlats($societyId)
    {
        return $this->getModel()
                    ->select('id', 'parent_id', 'name')
                    ->with([
                    'buildings' => function($q)
                    {
                        $q->select('id', 'parent_id', 'name');
                    },
                    'buildings.blocks' => function($q)
                    {
                        $q->select('id', 'society_id', 'block');
                    },
                     'buildings.blocks.flats' => function($q)
                    {
                        $q->whereStatus(1)
                          ->select('id', 'block_id', 'flat_no', 'type');
                    }])
                    ->find($societyId);
    }

    public function withBuildings()
    {
        $this->model = $this->model->with('buildings');

        return $this;
    }

    public function withBlocks()
    {
        $this->model = $this->model->with('buildings.blocks');

        return $this;
    }

    public function withFlats()
    {
        $this->model = $this->model->with('buildings.blocks.flats');

        return $this;
    }

    public function getBuildings($societyId)
    {
        return $this->model->whereParentId($societyId)->get();
    }
}
