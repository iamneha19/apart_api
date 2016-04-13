<?php namespace ApartmentApi\Listeners;

use ApartmentApi\Events\NewHelpdeskTicketWasLodged;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class EmailTicketDetails {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  NewHelpdeskTicketWasLodged  $event
	 * @return void
	 */
	public function handle(NewHelpdeskTicketWasLodged $event)
	{
		//
	}

}
