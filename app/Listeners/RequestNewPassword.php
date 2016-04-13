<?php namespace ApartmentApi\Listeners;

use ApartmentApi\Events\ResetPassword;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Mail\Mailer;

class RequestNewPassword {

	protected $mailer;
	
	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct(Mailer $mailer)
	{
//	print_r("test");exit;	
            $this->mailer = $mailer;
                
	}

	/**
	 * Handle the event.
	 *
	 * @param  SocietyWasRegistered  $event
	 * @return void
	 */
	public function handle(ResetPassword $event)
	{
//            print_r($event);exit;
		$this->mailer->send('emails.generate_new_pwd', ['pwd'=>$event->data], function($m) use ($event){
			$m->to($event->data['email'], $event->data['username'])
			->subject('Your Password Has Been Changed Successfully');
		});
	}

}
