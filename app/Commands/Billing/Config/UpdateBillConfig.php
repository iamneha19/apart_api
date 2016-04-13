<?php namespace ApartmentApi\Commands\Billing\Config;

use ApartmentApi\Commands\Command;
use Api\Commands\SelectCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Http\Request;
use ApartmentApi\Models\BillingConfig;

class UpdateBillConfig extends SelectCommand
{
    protected $societyId;

    protected $key;

    protected $value;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($societyId, $key, $value)
	{
		$this->societyId = $societyId;

        $this->key = $key;

        $this->value = $value;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(BillingConfig $billingConfig)
	{
        $config = $billingConfig::where([
                    'society_id' => $this->societyId,
                    'key'        => $this->key
                ])->first();

        $response = $config ?
            $config->update([
                'value' => $this->value
            ]):
            false;

        return $response ?
                    $this->make200Response('Successfully saved.'):
                    $this->make404Response('Not Found by given key' . $this->key . '.');
	}

}
