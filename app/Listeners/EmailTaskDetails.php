<?php namespace ApartmentApi\Listeners;

use ApartmentApi\Events\TaskWasCreated;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Mail\Mailer;

class EmailTaskDetails {

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
	public function handle(TaskWasCreated $event)
	{
//            print_r($event);exit;
		$this->mailer->send('emails.new_task_creation', ['task'=>$event->data], function($m) use ($event){
			$m->to($event->data['to_email'], $event->data['assign_to'])
                        ->replyTo($event->data['from_email'])
			->subject('New Task');
		});
	}

}
