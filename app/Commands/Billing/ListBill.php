<?php namespace ApartmentApi\Commands\Billing;

use Api\Commands\SelectCommand;
use ApartmentApi\Repositories\BillingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ListBill extends SelectCommand
{
    protected $rules = [
        'society_id' => 'required',
    ];

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Request $request)
	{
		parent::__construct($request);
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(BillingRepository $repo)
	{
        if ($this->validationFails(new Validator)) {
            return $this->makeErrorResponse('Society id is required.', 400);
        }

        return $repo->societyIs($this->get('society_id'))
                    ->fewSelection()
                    ->withBuildings()
                    ->withFlatsDetails(true, true, false)
                    ->orderBy('updated_at', 'DESC')
                    ->paginate();
	}

}
