<?php namespace ApartmentApi\Commands\Billing\Society;

use ApartmentApi\Commands\Command;
use ApartmentApi\Models\FlatBill;
use Api\Commands\UpdateCommand;
use Illuminate\Contracts\Bus\SelfHandling;

class FlatBillPayment extends UpdateCommand
{
    protected $rules = [
        'payment_type'  => 'required',
        'cheque_number' => 'required_if:payment_type,cheque',
    ];

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(FlatBill $flatBill, \Illuminate\Bus\Dispatcher $dispatcher)
	{     
             
		if ($flatBill = $flatBill->find($this->get('id'))) {
            
            if ($flatBill->status == 'Paid') {
                return $this->make400Response('Bill is already payed.');
            }

            $flatBill->payment()->create([
                         'payment_type' => $this->get('payment_type'),
                         'cheque_number' => $this->get('cheque_number')
                     ]);
            
             $flatBill->update(['status' => 'paid']);
             
             
             if (! $dispatcher->dispatch(new SendBillReceipt($flatBill))) {
//                dd('d');
            }
           
             return $this->make200Response('Bill Successfully marked as payed.');
        }
        

        return $this->make200Response('Flat bill not found by given id.');
	}

}
