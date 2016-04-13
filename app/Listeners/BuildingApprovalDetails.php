<?php namespace ApartmentApi\Listeners;

use ApartmentApi\Events\BuildingApprovedStatus;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Mail\Mailer;

class BuildingApprovalDetails {

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
	public function handle(BuildingApprovedStatus $event)
	{
		$this->mailer->send('emails.building_status', ['status'=>$event->data], function($m) use ($event){
			$m->to($event->data['to_email'], $event->data['to_name'])
//              $m->to('neha.agrawal@sts.in', 'Neha Agrawal')
                        ->replyTo($event->data['from_email'])
                        ->subject('Regarding Building Approval Status');
		});
	}

}
