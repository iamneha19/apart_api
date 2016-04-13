<?php namespace ApartmentApi\Listeners;

use ApartmentApi\Events\SocietyJoinRequest;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Mail\Mailer;

class EmailUserOnJoinRequest {

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
	 * @param  SocietyJoinRequest  $event
	 * @return void
	 */
	public function handle(SocietyJoinRequest $event)
	{
		$this->mailer->send('emails.user_join_request', ['user'=>$event->data], function($m) use ($event){
			$m->to($event->data['email'], $event->data['name'])
			->subject('Account');
		});
	}

}
