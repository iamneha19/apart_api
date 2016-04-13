<?php

namespace ApartmentApi\Commands\Billing\Traits;

use ArrayComparator\ArrayComparator;
use Illuminate\Support\Collection;

/**
 * Flat And building comparison
 *
 * @author Mohammed Mudasir
 */
trait FlatBuildingComparisonTrait
{
    protected $arrayComparator;

    public function getArrayComparator()
    {
        return $this->arrayComparator ?: $this->arrayComparator = new ArrayComparator;
    }

    public function flatOrBuildingExist(Collection $models, array $flatsId, array $buildingsId)
    {
        foreach ($models as $model) {
            if ($model->id) {
                $modelFlatsId = $model->flats->lists('id');
                $modelBuildingsId = $model->buildings->lists('id');

                // Check flat same flat's exists
                if (count($flatsId) > 0 and $this->flatsExist($modelFlatsId, $flatsId)) {
                    return true;
                }

                // Check flat same building's exists
                if (count($buildingsId) > 0 and $this->buildingsExist($modelBuildingsId, $buildingsId)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function flatsExist($modelFlatsId, $flatsId)
    {
        $flatExists = false;

        if (count($modelFlatsId) == count($flatsId)) {
            $this->getArrayComparator()
                ->whenEqual(function($modelFlatsId, $flatsId) use (&$flatExists) {
                    $flatExists = true;
                })->compare($modelFlatsId, $flatsId);
        }

        return $flatExists;
    }

    public function buildingsExist($modelBuildingsId, $buildingsId)
    {
        $buildingExists = false;

        // Checking array count is not same Note: Saving process
        if (count($modelBuildingsId) == count($buildingsId)) {
            $this->getArrayComparator()
                ->whenEqual(function($modelBuildingsId, $buildingsId) use (&$buildingExists) {
                    $buildingExists = true;
                })
                ->compare($modelBuildingsId, $buildingsId);
        }

        return $buildingExists;
    }
}
