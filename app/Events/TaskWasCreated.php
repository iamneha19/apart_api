<?php namespace ApartmentApi\Events;

use ApartmentApi\Events\Event;

use Illuminate\Queue\SerializesModels;

class TaskWasCreated extends Event {

	use SerializesModels;
	
	public $data;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($data)
	{
		$this->data = $data;
//                print_r($data);exit;
	}

}
