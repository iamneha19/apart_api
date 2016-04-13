<?php namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Requests;
use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Http\Requests\FlatBillPaymentRequest;
use ApartmentApi\Commands\Billing\Society\GetFlatBills;
use ApartmentApi\Commands\Billing\Society\FlatBillReport;
use ApartmentApi\Commands\Billing\Society\FlatBillPayment;
use ApartmentApi\Repositories\FlatBillRepository;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Exception;

class FlatBillController extends ApiController
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($societyId, $year, $month, Request $request)
	{
        $job = new GetFlatBills($societyId, new Request(array_merge($request->all(), ['bill_month' => "$month $year"])));

        if ($flatBills = $this->dispatch($job)) {
            return $this->presentor()->make200Response('Successfully loaded flat bill\'s', $flatBills);
        }

        return $this->presentor()
                    ->makeResponseByCode(
                            $job->getMessage(),
                            $job->getStatusCode(),
                            $job->getError()
                    );
	}

    public function generate($societyId, Request $request)
    {
        if (! $request->get('month')) {
            return $this->presentor()->make400Response('Month is required.');
        }

        $date = Carbon::parse($request->get('month'))->format('Y-m');
        $sendMail = $request->get('publish', false);

        Artisan::call("publish:bills", [
            'society-id' => $societyId,
            'year-month' => $date,
            '--send-mail' => $sendMail
        ]);

        return $this->presentor()->make200Response('Successfully generated.');
    }

    public function payment($id, FlatBillPaymentRequest $request)
    {
        return $this->dispatch(FlatBillPayment::instance($id, $request));
    }

    public function report($societyId, Request $request)
    {
        return $this->dispatch(FlatBillReport::instance($societyId, $request));
    }

}
