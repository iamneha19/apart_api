<?php namespace ApartmentApi\Listeners;

use ApartmentApi\Events\UserWasCreated;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Mail\Mailer;

class EmailUserDetails {

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
	 * @param  UserWasCreated  $event
	 * @return void
	 */
	public function handle(UserWasCreated $event)
	{
		$this->mailer->send('emails.welcome_user', ['user'=>$event->data], function($m) use ($event){
			$m->to($event->data['email'], $event->data['name'])
			->subject('Welcome to '.  getenv('PROJECT_NAME'));
		});
	}

}
