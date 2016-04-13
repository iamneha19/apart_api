<?php namespace ApartmentApi\Listeners;

use ApartmentApi\Events\BillReceipt;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Mail\Mailer;

class BillReceiptDetails {

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
	public function handle(BillReceipt $event)
	{
//            print_r($event);exit;
		$this->mailer->send('emails.generate_bill_receipt', ['task'=>$event->data], function($m) use ($event){
			$m->to($event->data['to_email'], $event->data['first_name'].' '.$event->data['last_name'])->subject('Bill Receipt');
            $m->attach($event->data['file']);
		});
	}

}
