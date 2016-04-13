<?php namespace ApartmentApi\Listeners;

use ApartmentApi\Events\TaskWasUpdated;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Mail\Mailer;

class EmailTaskUpdateDetails {

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
	public function handle(TaskWasUpdated $event)
	{
//            print_r($event);exit;
		$this->mailer->send('emails.task_update', ['task'=>$event->data], function($m) use ($event){
                        if($event->data['type']=='')
                        {
                            $m->to($event->data['to_email'], $event->data['assign_to'])
                            ->replyTo($event->data['from_email'])
                            ->subject('Updated Task');
                        }if($event->data['type']=='C'){
                            $m->to($event->data['to_email'], $event->data['assign_to'])
                            ->replyTo($event->data['from_email'])
                            ->subject('Closed Task');
                        }
                        if($event->data['type']=='O')
                        {
                             $m->to($event->data['to_email'], $event->data['assign_to'])
                            ->replyTo($event->data['from_email'])
                            ->subject('Re-open Task');
                        }
		});
	}

}
