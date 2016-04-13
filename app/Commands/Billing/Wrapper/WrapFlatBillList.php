<?php

namespace ApartmentApi\Commands\Billing\Wrapper;

use ApartmentApi\Commands\Command;
use ApartmentApi\Commands\Billing\Config\GetBillConfig;
use ApartmentApi\Commands\Billing\Wrapper\WrapBillConfig;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Collection;
use Illuminate\Bus\Dispatcher;

/**
 * Wrapper for flat bill
 *
 * @author Mohammed Mudasir
 */
class WrapFlatBillList extends Command implements SelfHandling
{
    protected $flatBills;

    protected $billConfig;

	public function __construct($flatBills)
	{
        $this->flatBills = $flatBills;
	}

    public function handle(Dispatcher $dispatcher)
    {
        $billConfig = $dispatcher->dispatch(new GetBillConfig($this->flatBills->first()->society_id, null, true));

        foreach ($this->flatBills as $index => $flatBill) {
            // Add all charges
            $total = $flatBill->charge;
            $total += array_sum($flatBill->flatBillItems->lists('item.charge'));

            foreach ($flatBill->flatBillItems as $index => $flatBillItem) {
                if (is_null($flatBillItem->item)) {
                    $flatBill->flatBillItems->forget($index);
                }
            }

            // If square feet is not given then 1 will be default
            $total *= $flatBill->flat->square_feet_1 ?: 1;

            $total += $total * $billConfig->get('service_tax') / 100;
            $flatBill->setAttribute('service_tax', $billConfig->get('service_tax'));

            // Adding select2 support
            $flatBill->setAttribute('select2', $flatBill->flat ? $flatBill->flat->jQuerySelect2: null);

            // Setting total charge
            $flatBill->setAttribute('total_charge', number_format($total));
        }

        return $this->flatBills;
    }
}
