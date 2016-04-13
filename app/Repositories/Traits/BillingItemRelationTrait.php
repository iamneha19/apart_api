<?php

namespace ApartmentApi\Repositories\Traits;

/**
 * Billing Item Relations
 *
 * @author Mohammed Mudasir
 */
trait BillingItemRelationTrait
{
    public function withFlats()
    {
        $this->model = $this->model->with(['flats' => function($q)
        {
            $q->isActive()->select('flat.id', 'flat.type', 'flat.flat_no', 'flat.block_id');
        }]);

        return $this;
    }

    public function withBuildings($loadOtherRelationship = false)
    {
        $this->model = $this->model->with(['buildings' => function($q)
        {
            $q->select('society.id', 'name');
        }]);

        return $this;
    }

    public function societyIs($id)
    {
        $this->model = $this->model->whereSocietyId($id);

        return $this;
    }

    public function withFlatsDetails($withBuilding = true, $withBlock = true, $withFlat = true)
    {
        if ($withBuilding) {
            $this->model = $this->model->with([
                'flats.flatDetails.building' => function($q) {
                    $q->select('id', 'name');
                }]);
        }

        if ($withBlock) {
            $this->model = $this->model->with([
                'flats.flatDetails.block' => function($q) {
                    $q->select('id', 'block');
                }]);
        }

        if ($withFlat) {
            $this->model = $this->model->with([
                'flats.flatDetails.flat' => function($q) {
                    $q->select('id', 'flat_no');
                }]);
        }

        return $this->withFlats();
    }
}
