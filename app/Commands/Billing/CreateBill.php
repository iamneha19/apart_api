<?php 

namespace ApartmentApi\Commands\Billing;

use Api\Commands\CreateCommand;
use ApartmentApi\Models\Billing;
use DB;
use Illuminate\Http\Request;
use ApartmentApi\Http\Requests\BillingRequest;
use Carbon\Carbon;
use ApartmentApi\Commands\Billing\Traits\FlatBuildingComparisonTrait;

class CreateBill extends CreateCommand
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
    ];

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle(Billing $model)
    {
        $bill = $this->only('office_charge', 'shop_charge', 'residential_charge', 'month', 'society_id');

        $date = Carbon::parse($this->get('month'))->format('Y-m-d');

        $bill = array_merge($bill, [
                'month' => $date
            ]);

        $flatsId = $this->get('flats') ?: [];

        $buildingsId = $this->get('buildings')?: [];

        return $this->create($model, $bill, $flatsId, $buildingsId);
    }

    public function create($model, $bill, $flatsId, $buildingsId)
    {
        return DB::transaction(function() use ($model, $bill, $flatsId, $buildingsId) {
            $models = $model->withOnlyFlatsAndBuildingsId()
                            ->where([
                                'month'      => $bill['month'],
                                'society_id' => $bill['society_id']
                            ])->get();
                            
            if ($this->flatOrBuildingExist($models, $flatsId, $buildingsId)) {
                return $this->billAlreadyExistResponse();
            }

            $model = $model->fill([
                            'month'      => $bill['month'],
                            'society_id' => $bill['society_id']
                        ])->create($bill);

            $model->flats()
                  ->sync($flatsId);

            $model->buildings()
                  ->sync($buildingsId);

            return $this->makeSuccessResponse('Successfully created bill.');
        });
    }

    public function billAlreadyExistResponse()
    {
        return $this->makeErrorResponse('Bill already exists by same month, flats or buildings.', 400);
    }

}
