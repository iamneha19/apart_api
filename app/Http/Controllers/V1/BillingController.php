<?php

namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Requests;
use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Http\Requests\BillingRequest;
use ApartmentApi\Http\Requests\SocietyIdRequest;
use ApartmentApi\Http\Requests\BillingItemRequest;
use ApartmentApi\Commands\Billing\CreateBill;
use ApartmentApi\Commands\Billing\CreateBillingItem;
use ApartmentApi\Http\Controllers\V1\ApiController;
use ApartmentApi\Commands\Billing\ListBill;
use ApartmentApi\Commands\Billing\FindBill;
use ApartmentApi\Commands\Billing\UpdateBill;
use ApartmentApi\Repositories\BillingRepository;
use ApartmentApi\Models\FlatBill;

use Illuminate\Http\Request;

class BillingController extends ApiController
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(ListBill $command)
	{
         $bills = $this->dispatch($command);

         return $bills ?
            $this->presentor()->make200Response('Successfully loaded all billing items.', $bills):
            $this->presentor()->makeResponseByCode($command->getError(), $command->getStatusCode());
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(BillingRequest $request)
	{
        $command = new CreateBill($request);

        return ($this->dispatch($command)) ?
            $this->presentor()->make200Response($command->getMessage()):
            $this->presentor()->makeResponseByCode($command->getError(), $command->getStatusCode());
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id, SocietyIdRequest $request, BillingRepository $repo)
	{
		$node = $this->dispatch(new FindBill($id, $request));

        if (count($node) > 0 and $node) {
            return $this->presentor()->make200Response('Bill found.', $node);
        }

        return $this->presentor()->make404Response("Bill by id $id, not found.");
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id, BillingRequest $request, BillingRepository $repo)
	{
        $job = new UpdateBill($id, $request);

        $results = $this->show($id, new SocietyIdRequest($request->all()), $repo);

        return ($this->dispatch($job)) ?
            $this->presentor()->make200Response($job->getMessage(), $results):
            $this->presentor()->makeResponseByCode($job->getError(), $job->getStatusCode());
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id, SocietyIdRequest $request, BillingRepository $billingRepo)
	{
        $societyId = $request->get('society_id');

		if ($responseCode = $billingRepo->societyIs($societyId)->delete($id)) {
            return ($responseCode === 404) ?
                 $this->presentor()->make404Response("Node not found with given id."):
                 $this->presentor()->make200Response('Successfully Deleted');
        }

        return $this->presentor()->make500Response('Found internal error while deleting');
	}

}
