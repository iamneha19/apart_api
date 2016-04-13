<?php namespace ApartmentApi\Listeners;

use ApartmentApi\Events\SocietyWasRegistered;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Mail\Mailer;

class EmailSocietyAndUserDetails {

	protected $mailer;
	
	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct(Mailer $mailer)
	{
		$this->mailer = $mailer;
	}

	/**
	 * Handle the event.
	 *
	 * @param  SocietyWasRegistered  $event
	 * @return void
	 */
	public function handle(SocietyWasRegistered $event)
	{
		$this->mailer->send('emails.new_society_registration', ['user'=>$event->data], function($m) use ($event){
			$m->to($event->data['email'], $event->data['name'])
			->subject('New Society Registration');
		});
	}

}
