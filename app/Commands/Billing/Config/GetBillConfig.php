<?php

namespace ApartmentApi\Commands\Billing\Config;

use ApartmentApi\Commands\Command;
use ApartmentApi\Commands\Billing\Wrapper\WrapBillConfig;
use ApartmentApi\Models\BillingConfig;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Collection;
use Illuminate\Bus\Dispatcher;
use Api\Traits\InstantiableTrait;

class GetBillConfig extends Command implements SelfHandling
{
    use InstantiableTrait;

    private $societyId;

    private $key;

    private $wrapped;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($societyId, $key = null, $wrapped = false)
	{
		$this->societyId = $societyId;

        $this->key = $key;

        $this->wrapped = $wrapped;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(BillingConfig $billingConfig, Dispatcher $dispatcher)
	{
        $billingConfig = $billingConfig->whereSocietyId($this->societyId);

        $rawConfig = $this->key ?
                    $billingConfig->whereKey($this->key)->first():
                    $billingConfig->get();

        if ($rawConfig instanceof Collection and $this->wrapped) {
            return $dispatcher->dispatch(new WrapBillConfig($rawConfig));
        }

        return $rawConfig;
	}

}
