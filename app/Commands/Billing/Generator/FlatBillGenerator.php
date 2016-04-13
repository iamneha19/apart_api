<?php namespace ApartmentApi\Commands\Billing\Generator;

use ApartmentApi\Commands\Command;

use ApartmentApi\Models\Flat;
use ApartmentApi\Models\Billing;
use Api\Traits\InstantiableTrait;
use Api\Traits\ApiResponseTrait;
use Api\Commands\SelectCommand;
use DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use ApartmentApi\Repositories\FlatBillRepository;

class FlatBillGenerator extends SelectCommand
{
    use InstantiableTrait, ApiResponseTrait;

    protected $flat;

    protected $bill;

    protected $date;

    protected $console;

    protected $flatBillRepo;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Flat $flat, Billing $bill, Carbon $date = null, $console = null)
	{
		$this->flat = $flat;

        $this->bill = $bill;

        $this->date = $date;

        $this->console = $console;
	}

	/**
	 * Criteria to generate flat bill are
	 *
	 *
	 * @return void
	 */
	public function handle(FlatBillRepository $flatBillRepo)
	{
        $this->flatBillRepo = $flatBillRepo;

        $data = $this->getPriorityAndMaintanceCharge();

        $data['month'] = $this->bill->month;
        $data['society_id'] = $this->bill->society_id;
        $data['bill_id'] = $this->bill->id;

        $flatBill = $flatBillRepo->syncFlatBill($data['society_id'], $this->flat->id, $data['month']);

        if ($flatBill) {
            // If current flat bill has (more weightage)
            if ($flatBill->priority < $data['priority'] and ! is_null($flatBill->priority)) {
                return true;
            }

            if (! $flatBill->update($data)) {
                $this->error = 'Flat bill found but unable to update or add bill charges. Hence priority is ' .
                                    $data['priority'] . ' and charges is ' . $data['charge'];

                return false;
            }

            return true;
        }

        $this->error = 'Unable to get by flat id ' . $this->flat->id;

		return false;
	}

    /**
     * Get Priority and Maintaince charge
     *
     * @return [type] [description]
     */
    public function getPriorityAndMaintanceCharge()
    {
        $data = ['priority' => 3];

        // If flat id is in bill flat section then its priority one
        // Note: This is performance issue because it must be relace by eager loading
        if ($this->bill->flats()->whereFlatId($this->flat->id)->count()) {
            $data['priority'] = 1;
        }

        // If flat building id is in bill building section then its pririty two
        // Note: This is performance issue because it must be relace by eager loading
        if ($this->bill->buildings()->whereBuildingId($this->flat->userSociety->building->id)->count()) {
            $data['priority'] = 2;
        }

        $data['charge'] = $this->getMaintanceByFlatType();

        return $data;
    }

    public function getMaintanceByFlatType(Billing $bill = null)
    {
        $bill ?: $bill = $this->bill;

        switch ($this->flat->type) {
            case 'flat':
                return $bill->residential_charge;

            case 'office':
                return $bill->office_charge;

            case 'shop':
                return $bill->shop_charge;

            default:
                return $bill->residential_charge;
        }
    }

}
