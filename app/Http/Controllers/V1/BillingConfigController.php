<?php namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Requests;
use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Http\Requests\BillingConfigRequest;
use ApartmentApi\Commands\Billing\Config\SaveBillConfig;
use ApartmentApi\Commands\Billing\Config\UpdateBillConfig;
use ApartmentApi\Commands\Billing\Config\GetBillConfig;
use ApartmentApi\Models\BillingConfig;

use Illuminate\Http\Request;

class BillingConfigController extends ApiController
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($societyId, BillingConfig $model)
	{
        $results = $model->where(['society_id' => $societyId])->lists('value', 'key');

		return count($results) > 0 ?
                    $this->presentor()->make200Response('Billing configuration loaded.', $results):
                    $this->presentor()->make404Response('No bill configuration found.');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store($societyId, BillingConfigRequest $request)
	{
        foreach ($request->except('access_token') as $key => $value) {
            $command = new SaveBillConfig($societyId, $key, $value);

            if (! $this->dispatch($command)) {
                return $this->presentor()->makeResponseByCode($command->getError(), $command->getStatusCode());
            }
        }

        return $this->presentor()->make200Response('Successfully bill configuration saved.');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($societyId, $key)
	{
		$job = new GetBillConfig($societyId, $key);

        return $this->dispatch($job);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($societyId, BillingConfigRequest $request)
	{
		$job = new UpdateBillConfig($societyId, $request->get('key'), $request->get('value'));

        return $this->dispatch($job);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($societyId, $key, BillingConfig $billingConfig)
	{
		return $billingConfig
                ->where([
                    'society_id' => $societyId,
                    'key' => $key
                ])->delete() ?
                    $this->presentor->make200Response('Successfully deleted'):
                    $this->presentor->make500Response('Unable to delete.');

	}

}
