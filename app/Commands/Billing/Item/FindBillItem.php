<?php namespace ApartmentApi\Commands\Billing\Item;

use Api\Commands\SelectCommand;
use ApartmentApi\Repositories\BillingItemRepository;
use Carbon\Carbon;

class FindBillItem extends SelectCommand
{
    protected $rules = [
        'society_id' => 'required'
    ];

    public function __construct($id, $request)
    {
        $this->id = $id;

        $this->societyId = $request->get('society_id');

        parent::__construct($request);
    }

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(BillingItemRepository $repo)
	{
        $node = $repo->societyIs($this->societyId)
                     ->fewSelection()
                     ->withFlats()
                     ->withBuildings()
                     ->find($this->id, true);

		if (! $node) {
			return false;
		}

        $arrayNode = $node->toArray();

        $arrayNode['flats'] = $arrayNode['buildings'] = [];

        foreach ($node->flats as $flat) {
            array_push($arrayNode['flats'], $flat->jQuerySelect2);
        }

        foreach ($node->buildings as $building) {
            array_push($arrayNode['buildings'], $building->jQuerySelect2);
        }

        $arrayNode['month'] = Carbon::parse($node->month)->format('F Y');

        return $arrayNode;
	}

}
