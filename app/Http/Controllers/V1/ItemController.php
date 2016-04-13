<?php

namespace ApartmentApi\Http\Controllers\V1;

use Illuminate\Http\Request;
use ApartmentApi\Http\Requests\SocietyIdRequest;
use ApartmentApi\Http\Requests\BillingItemRequest;
use ApartmentApi\Commands\Billing\Item\CreateBillItem;
use ApartmentApi\Commands\Billing\Item\UpdateBillItem;
use ApartmentApi\Commands\Billing\Item\ListBillItem;
use ApartmentApi\Commands\Billing\Item\FindBillItem;
use ApartmentApi\Repositories\BillingItemRepository;

class ItemController extends ApiController
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(SocietyIdRequest $request)
	{
        $command = new ListBillItem($request);

        $items = $this->dispatch($command);

        return count($items) > 0 ?
                $this->presentor->make200Response('Successfully loaded all billing items.', $items):
                $this->presentor->make404Response('Items not found.');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(BillingItemRequest $request)
	{
        $command = new CreateBillItem($request);

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
	public function show($id, Request $request, BillingItemRepository $repo)
	{
        $node = $this->dispatch(new FindBillItem($id, $request));

        if ($node) {
            return $this->presentor()->make200Response('Billing Item found.', $node);
        }

        return $this->presentor()->make404Response("Item node $id not found.");
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id, BillingItemRequest $request, BillingItemRepository $repo)
	{
        $command = new UpdateBillItem($id, $request);

        return ($this->dispatch($command)) ?
            $this->presentor()->make200Response($command->getMessage(), $this->show($id, $request, $repo)):
            $this->presentor()->makeResponseByCode($command->getError(), $command->getStatusCode());
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id, BillingItemRepository $itemRepository)
	{
        $responseCode = $itemRepository->delete($id);

        return ($responseCode) ?
             $this->presentor()->make200Response('Successfully Deleted'):
              $this->presentor()->make404Response("Bill item not found by given id.");

	}

}
