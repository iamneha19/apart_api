<?php 

namespace ApartmentApi\Commands\Billing\Society;

use ApartmentApi\Commands\Command;
use HTML2PDF;
use Illuminate\Contracts\Bus\SelfHandling;

class SendBillReceipt extends Command implements SelfHandling 
{
    use \Api\Traits\ApiResponseTrait;
    
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(\ApartmentApi\Models\FlatBill $flatBill)
	{
		$this->flatBill = $flatBill;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
        $flatBillRepo = new \ApartmentApi\Repositories\FlatBillRepository($this->flatBill);
        $html2pdf = new HTML2PDF('P','A4','en');
		$doc = view('pdf.bill_receipt', ['flatBill' => $flatBillRepo->getReceiptSpecificDetails()])->render();
        $id = $this->flatBill->id;
        $user_data = $flatBillRepo->getReceiptSpecificDetails();
        $first_name = $user_data->flat->flatDetails->user->first_name;
        $last_name =  $user_data->flat->flatDetails->user->last_name;
        $email = $user_data->flat->flatDetails->user->email;
		$html2pdf->writeHTML($doc,false);
		$html2pdf->Output(base_path().'/storage/app/BillReceipt_'.$id.'.pdf','F');
		$file=base_path().'/storage/app/BillReceipt_'.$id.'.pdf';
        $data = array(
                        'first_name'=>$first_name,
                        'last_name'=>$last_name,
                        'to_email'=>$email,
                        'file'=>$file,
                            );
                event(new \ApartmentApi\Events\BillReceipt($data));
        return $this->make200Response('Bill Successfully marked as payed.');
	}

}
