<?php namespace ApartmentApi\Events;

use ApartmentApi\Events\Event;

use Illuminate\Queue\SerializesModels;

/*
 *	Event to send society configuration data imported mail to Chairperson. 
 * 
 *	@category	Society Configuration
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 * 
 */

class SocietyConfigUploaded extends Event {

	use SerializesModels;
	
	public $data;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
	}

}
