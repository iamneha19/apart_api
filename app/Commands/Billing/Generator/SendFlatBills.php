<?php

namespace ApartmentApi\Commands\Billing\Generator;

use ApartmentApi\Commands\Command;
use ApartmentApi\Commands\Billing\Generator\GenerateFlatBillInvoice;
use ApartmentApi\Commands\Billing\Wrapper\WrapFlatBillList;
use ApartmentApi\Repositories\FlatBillRepository;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Dispatcher;
use Carbon\Carbon;
use Mail;

/**
 * Send Flat bills to all flat user
 *
 * @author Mohammed Mudasir
 */
class SendFlatBills extends Command implements SelfHandling
{
	use InteractsWithQueue, SerializesModels;

    protected $societyId;

    protected $date;

    protected $console;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($societyId, $date, $console = null)
	{
		$this->societyId = $societyId;

        $this->date = $date;

        $this->console = $console;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(FlatBillRepository $flatBillRepo, Dispatcher $dispatcher)
	{
        $flatBills = $flatBillRepo
                        ->flatBillsQuery(
                                $this->societyId,
                                $this->carbonDate(),
                                null,
                                ['id', 'name', 'address'],
                                ['id', 'first_name', 'last_name', 'email', 'contact_no'])
                        ->get();

        $wrapFlatBills = $dispatcher->dispatch(new WrapFlatBillList($flatBills));

        foreach ($wrapFlatBills as $flatBill) {
            $pdfGenerator = new GenerateFlatBillInvoice($flatBill);

            $filePath = $dispatcher->dispatch($pdfGenerator);

            $this->triggerMail($flatBill, $filePath);
        }
	}

    private function triggerMail($flatBill, $filePath)
    {
        $name   = $flatBill->flat->flatDetails->user->fullName;
        $email  = $flatBill->flat->flatDetails->user->email;
        $month  = $flatBill->month;

        Mail::queueOn('generated-flat-bills', 'emails.flat_bill', [
            'name' => $name,
            'month'=> $month,
            'flatDetail' => $flatBill->select2['text']
        ], function($message) use ($flatBill, $filePath, $name, $month, $email)
        {
            $message
            ->to($email, $name)
            ->subject("Flat Bill for the month of {$flatBill->month}.")
            ->attach($filePath, [
                'as'    => $flatBill->flat->flatDetails->user->fullName .'.pdf',
                'mime'  => 'application/pdf'
            ]);
        });
    }

    private function carbonDate()
    {
        return Carbon::parse($this->date);
    }

}
