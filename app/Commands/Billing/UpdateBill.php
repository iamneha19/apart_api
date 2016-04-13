<?php namespace ApartmentApi\Commands\Billing;

use Api\Commands\UpdateCommand;
use ApartmentApi\Models\Billing;
use DB;
use Carbon\Carbon;
use ApartmentApi\Commands\Billing\Traits\FlatBuildingComparisonTrait;

class UpdateBill extends UpdateCommand
{
    use FlatBuildingComparisonTrait;

    protected $rules = [
        'society_id' => 'required',
        'month' => 'required',
        'office_charge' => 'required',
        'shop_charge'   => 'required',
        'residential_charge' => 'required',
        'flats' => '',
        'buildings' => '',
        'flat_category'  => '',
    ];

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle(Billing $model)
    {
        $bill = $this->only('society_id', 'office_charge', 'shop_charge', 'residential_charge', 'month', 'flat_category');

        $date = Carbon::parse($this->get('month'))->format('Y-m-d');

        $bill = array_merge($bill, [
                'month' => $date
            ]);

        $flatsId = collect($this->get('flats'))->lists('id') ?: [];

        $buildingsId = collect($this->get('buildings'))->lists('id') ?: [];

        return $this->update($model, $bill, $flatsId, $buildingsId);
    }

    public function update($model, $bill, $flatsId, $buildingsId)
    {
        return DB::transaction(function() use ($model, $bill, $flatsId, $buildingsId)
        {
            $model = $model->firstOrNew([
                    'id'    => $this->get('id')
                ]);

            if (! $model->id) {
                return $this->makeErrorResponse('Bill does not exists.', 404);
            }

            if ($this->flatOrBuildingExist(
                    $model->withOnlyFlatsAndBuildingsId()
                          ->where([
                              'month'      => $bill['month'],
                              'society_id' => $bill['society_id']
                          ])
                          ->where('id', '!=', $model->id)->get()
                    , $flatsId, $buildingsId
                    )
                ) {
                return $this->makeErrorResponse('Bill already exist for same Month, Flats or Buildings.', 400);;
            }

            if (! $model->update($bill)) {
                return $this->makeErrorResponse('Unable to update bill.', 404);
            }

            $model->flats()
                  ->sync($flatsId);

            $model->buildings()
                  ->sync($buildingsId);

            return $this->makeSuccessResponse('Successfully updated bill.');
        });
    }

}
