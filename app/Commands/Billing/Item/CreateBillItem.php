<?php namespace ApartmentApi\Commands\Billing\Item;

use ApartmentApi\Commands\Command;
use ApartmentApi\Repositories\BillingRepository;
use ApartmentApi\Models\BillingItem;
use Api\Commands\CreateCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use Carbon\Carbon;
use DB;
use ApartmentApi\Commands\Billing\Traits\FlatBuildingComparisonTrait;

class CreateBillItem extends CreateCommand
{
    use FlatBuildingComparisonTrait;

    protected $rules = [
        'society_id' => 'required',
        'item_name' => 'required',
        'fixed_billing_item' => 'required',
        'flat_category' => 'required',
        'flat_type'     => 'required',
        'charge'        => 'required',
        'month'         => '',
        'flats'         => '',
        'buildings'     => '',
    ];

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(BillingItem $model)
	{
        $date = ($month = $this->get('month')) ?
                    Carbon::parse($month)->format('Y-m-d'):
                    NULL;

        $models = $model->withOnlyFlatsAndBuildingsId()
                        ->where([
                            'society_id' => $this->get('society_id'),
                            'name'  => $this->get('item_name'),
                            'month' => $date
                        ])->get();

        $flatsId = $this->get('flats') ?: [];

        $buildingsId = $this->get('buildings') ?: [];

        if ($this->flatOrBuildingExist(
                $model->withOnlyFlatsAndBuildingsId()
                      ->where([
                          'month'      => $date,
                          'society_id' => $this->get('society_id')
                      ])
                      ->where('name', $this->get('item_name'))
                      ->get()
                , $flatsId, $buildingsId)
            ) {
            return $this->billItemAlreadyExists();
        }

        $model->fill($this->only('charge', 'fixed_billing_item', 'flat_category', 'flat_type'));

        $model->fill([
            'society_id' => $this->get('society_id'),
            'name'  => $this->get('item_name'),
            'month' => $date
        ]);

        if ($this->get('fixed_billing_item') == 'YES') {
            unset($model->month);
        }

        return $this->create($model, $flatsId, $buildingsId);
	}

    public function create($model, $flatsId, $buildingsId)
    {
        return DB::transaction(function() use ($model, $flatsId, $buildingsId) {
            if (! $model->save()) {
                return $this->makeErrorResponse('Unable to save bill item.', 500);
            }

            if ($flatsId) {
                $model->flats()
                      ->sync($flatsId);
            }

            if ($buildingsId) {
                $model->buildings()
                      ->sync($buildingsId);
            }

            return $this->makeSuccessResponse('Successfully created bill item.');
        });
    }

    public function billItemAlreadyExists()
    {
        return $this->makeErrorResponse('Bill item already exist by same name, month, flat and building.', 400);
    }

}
