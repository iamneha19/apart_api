<?php

namespace ApartmentApi\Commands\Billing\Generator;

use ApartmentApi\Commands\Command;
use ApartmentApi\Models\FlatBill;
use HTML2PDF;

use Illuminate\Contracts\Bus\SelfHandling;

class GenerateFlatBillInvoice extends Command implements SelfHandling
{
    protected $flatBill;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(FlatBill $flatBill)
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
        $pdfGenerator = new HTML2PDF('P', 'A4', 'de', false, 'UTF-8');

        $doc = view('pdf.invoice_sample', ['flatBill' => $this->flatBill])->render();

        $pdfGenerator->writeHTML($doc, false);

        $pdfGenerator->Output($this->pdfFilePath(), 'F');

        return $this->pdfFilePath();
	}

    protected function pdfFilePath()
    {
        return base_path("storage/app/flat-bill/{$this->flatBill->id}.pdf");
    }

}
