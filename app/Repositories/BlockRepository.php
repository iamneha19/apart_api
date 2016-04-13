<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\Block;
use Repository\Repository;
use Illuminate\Support\Collection;

 /**
  * City Repository
  *
  * @author Mohammed Mudasir
  */
class BlockRepository extends Repository
{
    protected $model;

    protected $defaultSelection = ['id'];

    protected $fewSelection = ['id', 'name'];

    protected $perPage = 5;

    public function __construct(Block $model)
    {
        $this->model = $model;
    }

    public function whereSocietyId($societyId)
    {
        $this->model = $this->model->whereHas('society', function($q) use ($societyId)
        {
            $q->where('parent_id', $societyId);
        });

        return $this;
    }

    public function withSociety()
    {
        $args = func_num_args() > 0 ? func_get_args(): ['*'];

        $this->model = $this->model
                        ->addSelect('society_id')
                        ->with(['society' => function($q) use ($args)
                        {
                            call_user_func_array([$q, 'select'], $args);
                        }]);

        return $this;
    }

    public function searchBlockWithSociety($societyId, $blockContent = null)
    {
        return $this->fewSelection('id', 'block')
                    ->whereSocietyId($societyId)
                    ->withSociety('id', 'name')
                    ->whereLike('block', $blockContent)
                    ->get();
    }

    public function findWithSociety($blockId)
    {
        $this->model = $this->fewSelection('id', 'block')
                        ->model
                        ->whereId($blockId);

        return $this->withSociety()->first();
    }

}
