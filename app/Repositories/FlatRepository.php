<?php

namespace ApartmentApi\Repositories;

use Repository\Repository;
use ApartmentApi\Models\Flat;
use DB;
use Illuminate\Database\QueryException;

 /**
  * City Repository
  *
  * @author Mohammed Mudasir
  */
class FlatRepository extends Repository
{
    /**
     * Model For repository
     *
     * @var \ApartmentApi\Models\Flat
     */
    protected $model;

    /**
     * Default column Loaded in any circumstances
     *
     * @var array
     */
    protected $defaultSelection = ['id'];

    /**
     * Few Selection Columns which will be loaded when fewSelection method is called
     *
     * @var array
     */
    protected $fewSelection = ['id', 'flat_no', 'type','floor'];

    /**
     * Per page pagination item count
     * @var integer
     */
    protected $perPage = 5;

    public function __construct(Flat $model)
    {
        $this->model = $model;
    }

    /**
     * Get all those flats with block which has society
     *
     * @param  integer $societyId
     * @return \ApartmentApi\Models\Flat
     */
    public function getFlatsAndBlocksList(
                        $societyId,
                        $buildingId = null,
                        $blockId = null,
                        $attachedFlats = false)
    {
        $model = $this->hasSociety($societyId)
                    ->select('id', 'flat_no')
                    ->withBlock('id', 'society_id', 'block')
                    ->getModel();

        $model = $blockId ?
                    $model->whereHas('userSociety', function($q) use ($blockId) {
                        $q->where('block_id', $blockId);
                    }):
                    $model;

        $model = $buildingId ?
                    $model->whereHas('userSociety', function($q) use ($buildingId) {
                        $q->where('building_id', $buildingId);
                    }):
                    $model;

        $model = $attachedFlats ?
                    $model->whereHas('userSociety', function($q) {
                        $q->whereUserId(null);
                    }):
                    $model;

        return $model->get();
    }

    public function getFlatsBySocietyId($societyId)
    {
        return $this->hasSociety($societyId)
                    ->select('id', 'flat_no')
                    ->get();
    }

    public function getFlatsByBuildingId($buildingId)
    {
        return $this->model->whereHas('block.society', function($q) use ($buildingId) {
                        $q->whereId($buildingId);
                    })
                    ->select('id', 'flat_no')
                    ->get();
    }

    public function getFlatBlockBuildingList($societyId)
    {
        return $this->hasSociety($societyId)
                    ->select('id','floor', 'flat_no','square_feet_1', 'type', 'occupancy', 'status')
                    ->handleWithMethod('block.building')
                    ->handleWithMethod('userSociety', function($q) {
                        $q->select('id', 'society_id', 'building_id', 'user_id', 'block_id', 'flat_id', 'relation')
                          ->with(['block' => function($q) {
                              $q->select('id', 'block as name');
                          }, 'building' => function($q) {
                              $q->select('id', 'name');
                          }]);
                    })
                    ->orderBy('id', 'DESC')
                    ->paginate();
    }

    /**
     * Load with blocks
     *
     * @return self
     */
    public function withBlock()
    {
        $selections = func_get_args();

        $this->model = $this->model
                ->addSelect('block_id')
                ->with(['block' => function($q) use ($selections) {
                    ! count($selections) > 0 ?: call_user_func_array([$q, 'select'], $selections);
                }]);

        return $this;
    }

    /**
     * Load those flats which has society id given
     *
     * @param  integer  $id Society Id
     * @return self
     */
    public function hasSociety($id)
    {
        $this->model = $this->model
                ->whereHas('flatDetails', function($q) use ($id) {
                    $q->whereSocietyId($id);
                });

        return $this;
    }

    public function withUserSociety()
    {
        $selection = func_num_args() > 0 ? func_get_args() : ['*'];

        $this->model = $this->model
                            ->with(['userSociety' => function($q) use ($selection) {
                                call_user_func_array([$q, 'select'], $selection);
                            }]);

        return $this;
    }

    public function whereHasBuildings(array $buildingIds, $userRequired = true)
    {
        $this->model = $this->model->whereHas('flatDetails', function($q) use ($buildingIds, $userRequired) {
            $q->whereIn('building_id', $buildingIds);

            if ($userRequired) {
                $q->whereNotNull('user_id');
            }
        });

        return $this;
    }

    public function withBuilding()
    {
        $this->model = $this->model->with(['flatDetails.building' => function($q) {
            $q->select('*');
        }]);

        return $this;
    }

    public function add($attributes)
    {
//         dd($attributes);
        return DB::transaction(function() use ($attributes) {
            $collect = collect($attributes);
            $flat = $this->model
                        ->firstOrNew([
                            'flat_no' => $attributes['flat_no'],
                            'floor' => $attributes['floor'],
                            'block_id' => $attributes['block_id'],
                            'square_feet_1'=> $attributes['square_feet_1']
                        ]);
           
            if ($flat->id) {
                return false;
            } else {
                $flat->fill([
                        'type' => $attributes['type']
                    ])
                    ->save();
            }

            return $flat->userSociety()
                        ->create([
                            'society_id' => $attributes['society_id'],
                            'building_id'=> $attributes['building_id'],                           
                            'block_id'   => $attributes['block_id'],
                            'relation'   => $attributes['relation']
                        ]);
        });

    }

    public function edit($flatId, $attributes)
    {
      
        return DB::transaction(function() use ($attributes, $flatId) {
            $model = $this->model
                          ->findOrFail($flatId);

            $model->update($attributes);

            return $model->userSociety()
                        ->update([
                            'building_id'=> $attributes['building_id'],
                            'block_id'   => $attributes['block_id'],
                            'relation'   => $attributes['relation']
                        ]);
        });

    }

    public function delete($id)
    {
        return DB::transaction(function() use ($id) {
            $flat = $this->model->find($id);

            if (! $flat) {
                return false;
            }

            $userSociety = $flat->userSociety()->first();

            $flat->delete();
            return $userSociety->update([
                'flat_id' => null,
                'building_id' => null,
                'block_id' => null
            ]);
        });
    }

}
