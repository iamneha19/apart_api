<?php namespace ApartmentApi\Listeners;

use ApartmentApi\Events\AssociateMemberWasAdded;
use ApartmentApi\Events\SocietyWasRegistered;
use Illuminate\Mail\Mailer;

class EmailAssociateMemberDetails {

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
	public function handle(AssociateMemberWasAdded $event)
	{
		$this->mailer->send('emails.welcome_associate_member', ['user'=>$event->data], function($m) use ($event){
			$m->to($event->data['email'], $event->data['name'])
			->subject($event->data['society_name']);
		});
	}

}
