<?php namespace ApartmentApi\Commands\Billing\Society;

use ApartmentApi\Commands\Command;
use ApartmentApi\Repositories\FlatBillRepository;
use Api\Commands\SelectCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

class FlatBillReport extends SelectCommand
{
    protected $rules = [
        'year' => 'required'
    ];

    protected $societyId;

    protected $query;

    public function __construct($societyId, $request)
    {
        $this->societyId = $societyId;

        parent::__construct($request);
    }

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(FlatBillRepository $flatBillRepo)
	{
		$flatBills = $this->buildQuery($flatBillRepo)
                          ->fire();

        $months = [];

        foreach ($flatBills as $flatBill) {
            @$months[$flatBill->month]['month'] = $flatBill->month;
            @$months[$flatBill->month]['class'] = 'event-info';
            @$months[$flatBill->month]['total_flat_bills'] += 1;
            $months[$flatBill->month]['paid']['cash'] = @$months[$flatBill->month]['paid']['cash'];
            $months[$flatBill->month]['paid']['cheque'] = @$months[$flatBill->month]['paid']['cheque'];

            if ($flatBill->status == 'Unpaid') {
                @$months[$flatBill->month]['unpaid'] += 1;
            }
            else if ($flatBill->status == 'Paid') {
                if ($flatBill->payment->payment_type == 'cash') {
                    @$months[$flatBill->month]['paid']['cash'] += 1;
                    continue;
                }
                @$months[$flatBill->month]['paid']['cheque'] += 1;
            }

            // Title
            @$months[$flatBill->month]['title'] = "Total {$months[$flatBill->month]['total_flat_bills']} bill found, {$months[$flatBill->month]['unpaid']} unpaid.";
        }

        $months = array_values($months);

        return $this->make200Response('Successfully loaded.', $months);
	}

    public function buildQuery($flatBillRepo)
    {
        $this->query = $flatBillRepo
                        ->whereLike('month', "%" . $this->get('year'))
                        ->where(['society_id' => $this->societyId])
                        ->select('society_id', 'month', 'status')
                        ->withPayment('id', 'flat_bill_id', 'payment_type')
                        ->getModel();

        return $this;
    }

    public function fire()
    {
        return $this->query->get();
    }

}
