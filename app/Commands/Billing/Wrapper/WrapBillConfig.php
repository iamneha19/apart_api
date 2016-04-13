<?php

namespace ApartmentApi\Commands\Billing\Wrapper;


use ApartmentApi\Commands\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Collection;

/**
 * Wrapper Bill Config in a key value pair
 *
 * @author Mohammed Mudasir
 */
class WrapBillConfig extends Command implements SelfHandling
{
    protected $billingConfigs;

    protected $wrapper;

	public function __construct(Collection $billingConfigs)
	{
		$this->billingConfigs = $billingConfigs;

        $this->wrapper = collect();
	}

    public function handle()
    {
        foreach ($this->billingConfigs as $config) {
            $this->wrapper->put($config->key, $config->value);
        }

        return $this->wrapper;
    }
}
