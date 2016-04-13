<?php namespace ApartmentApi\Events\User;

use ApartmentApi\Events\Event;

use Illuminate\Queue\SerializesModels;

class UserIsLogging extends Event
{
	use SerializesModels;
}
