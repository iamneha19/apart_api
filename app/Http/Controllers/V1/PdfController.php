<?php

namespace ApartmentApi\Http\Controllers\V1;

use HTML2PDF;
use Response;
use ApartmentApi\Http\Controllers\NoticeBoardController;

class PdfController extends ApiController
{
	public function noticeBoard($itemId, NoticeBoardController $noticeBoardController)
	{
		$data = (array) $noticeBoardController->item($itemId);

		$html2pdf = new HTML2PDF('P','A4','de',false,'UTF-8');
		$doc = view('notice.print_view', $data)->render(); 
// 		$html2pdf->setDefaultFont('Arial');
		$html2pdf->writeHTML($doc,false);
		$html2pdf->Output(base_path().'/storage/app/NoticePdf.pdf','F');
		$file=base_path().'/storage/app/NoticePdf.pdf';
		return Response::download($file);
	}
}
