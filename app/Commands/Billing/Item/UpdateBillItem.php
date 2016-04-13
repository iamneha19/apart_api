<?php namespace ApartmentApi\Commands\Billing\Item;

use ApartmentApi\Commands\Command;
use ApartmentApi\Repositories\BillingRepository;
use ApartmentApi\Models\BillingItem;
use Api\Commands\UpdateCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use Carbon\Carbon;
use DB;
use ApartmentApi\Commands\Billing\Traits\FlatBuildingComparisonTrait;

class UpdateBillItem extends UpdateCommand
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
        'flats'  => '',
        'buildings' => '',
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
                    null;

        $model = $model->firstOrNew([
                'id'    => $this->get('id')
            ]);

        if (! $model->id) {
            return $this->makeErrorResponse('Bill item does not exists.', 404);
        }

        if ($this->get('fixed_billing_item') == 'YES') {
            $date = null;
        }

        $flatsId = collect($this->get('flats'))->lists('id') ?: [];

        $buildingsId = collect($this->get('buildings'))->lists('id') ?: [];

        if ($this->flatOrBuildingExist(
                $model->withOnlyFlatsAndBuildingsId()
                      ->where([
                          'month'      => $date,
                          'society_id' => $this->get('society_id')
                      ])
                      ->where('name', $this->get('item_name'))
                      ->where('id', '!=', $model->id)
                      ->get()
                , $flatsId, $buildingsId)
            ) {
            return $this->makeErrorResponse(
                    'Bill Item already exist by same Name, Month, Flats or Buildings.',
                    400);
        }

        $model = $model->find($this->get('id'))->fill([
                'society_id' => $this->get('society_id'),
                'name'  => $this->get('item_name'),
                'month' => $date
            ])
            ->fill($this->only('charge', 'fixed_billing_item', 'flat_category', 'flat_type'));

        return $this->update($model, $flatsId, $buildingsId);
	}

    public function update($model, $flatsId, $buildingsId)
    {
        return DB::transaction(function() use ($model, $flatsId, $buildingsId) {
            if (! $model->update()) {
                return $this->makeErrorResponse('Unable to save bill item.', 500);
            }

            $model->flats()
                  ->sync($flatsId);

            $model->buildings()
                ->sync($buildingsId);

            return $this->makeSuccessResponse('Successfully updated bill item.');
        });
    }

}
