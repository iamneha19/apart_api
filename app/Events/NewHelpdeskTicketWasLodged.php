<?php namespace ApartmentApi\Events;

use ApartmentApi\Events\Event;

use Illuminate\Queue\SerializesModels;

class NewHelpdeskTicketWasLodged extends Event {

	use SerializesModels;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

}
