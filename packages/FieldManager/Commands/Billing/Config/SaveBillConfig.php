<?php namespace ApartmentApi\Commands\Billing\Config;

use ApartmentApi\Commands\Command;
use Api\Commands\SelectCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Http\Request;
use ApartmentApi\Models\BillingConfig;

class SaveBillConfig extends SelectCommand
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
        $response = $billingConfig->firstOrCreate([
                    'society_id' => $this->societyId,
                    'key'        => $this->key,
                ])
                ->update([
    				'value'      => $this->value
                ]);

        return $response ?
                    $this->makeSuccessResponse('Successfully saved.'):
                    $this->makeErrorResponse('Unable to save.', 500);
	}

}
