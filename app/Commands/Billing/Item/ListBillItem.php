<?php namespace ApartmentApi\Commands\Billing\Item;

use ApartmentApi\Commands\Command;
use ApartmentApi\Repositories\BillingItemRepository;
use Api\Commands\SelectCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Http\Request;

class ListBillItem extends SelectCommand
{
    protected $rules = [
        'society_id' => 'required'
    ];

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(BillingItemRepository $repository)
	{
		return $repository
                    ->fewSelection()
                    ->societyIs($this->get('society_id'))
                    ->withFlatsDetails(true, true, false)
                    ->withBuildings()
                    ->paginate();
	}

}
