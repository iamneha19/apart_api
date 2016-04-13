<?php namespace ApartmentApi\Commands\Notification;

use Api\Commands\SelectCommand;
use ApartmentApi\Models\SocietyConfig;
use Illuminate\Contracts\Bus\SelfHandling;

/*
 *	Checks and returns chairman's remark. 
 * 
 *	@category	Wings Configuration
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 *	@deprecated	File deprecated in Release 2.2.1		
 * 
 */

class ListChaimanNotification extends SelectCommand {
	
	protected $societyId ;
	
	protected $rules = [
        'societyId' => 'required'
    ];

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($societyId)
	{
		$this->societyId = $societyId;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(SocietyConfig $societyConfig)
	{
		$notification = $societyConfig->whereSocietyId($this->societyId)->first()->toArray();
		
		return $notification;
	}

}
