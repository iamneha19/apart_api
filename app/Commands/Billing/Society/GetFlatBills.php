<?php namespace ApartmentApi\Commands\Billing\Society;

use ApartmentApi\Commands\Command;
use ApartmentApi\Commands\Billing\Wrapper\WrapFlatBillList;
use ApartmentApi\Repositories\FlatBillRepository;
use ApartmentApi\Presenters\BlockPresenter;
use Api\Commands\SelectCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Bus\Dispatcher;
use Carbon\Carbon;

class GetFlatBills extends SelectCommand
{
    protected $societyId;

    protected $flatBills;

    protected $flatBillRepo;

    protected $rules = [
        'bill_month'      => 'date_format:F Y',
        'status' => ''
    ];

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($societyId, Request $request)
	{
		$this->societyId = $societyId;

        parent::__construct($request);
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(FlatBillRepository $flatBillRepo, Dispatcher $dispatcher)
	{
        $this->flatBillRepo = $flatBillRepo;

        if (! $this->isValid()) return false;

        return (array) $dispatcher->dispatch(new WrapFlatBillList($this->getFlatBills()))->toArray();
	}

    protected function isValid()
    {
        if ($this->validationFails(new Validator)) {
            $this->setMessage('Validation failed, all fields are required.');
            return false;
        }

        return true;
    }

    public function getFlatBills()
    {
        return $this->flatBills ?: $this->setFlatBills();
    }

    private function setFlatBills()
    {
        return $this->flatBills = $this->flatBillRepo
                    ->getPaginatedList($this->societyId, $this->carbonDate(), $this->get('status'));
    }

    private function carbonDate()
    {
        return Carbon::parse($this->get('bill_month'));
    }

}
