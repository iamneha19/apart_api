<?php

namespace ApartmentApi\Commands\Billing\Generator;

use Illuminate\Bus\Dispatcher;
use Api\Traits\ApiResponseTrait;
use ApartmentApi\Repositories\FlatRepository;
use ApartmentApi\Repositories\BillingRepository;
use ApartmentApi\Repositories\SocietyRepository;
use ApartmentApi\Repositories\BillingItemRepository;
use ApartmentApi\Commands\Billing\Generator\Detacher;
use ApartmentApi\Commands\Billing\Generator\SendFlatBills;
use ApartmentApi\Commands\Billing\Generator\FlatsBillsGenerator;
use ApartmentApi\Commands\Billing\Generator\AttachFlatBillingItems;
use Api\Traits\InstantiableTrait;
use DB;
use ApartmentApi\Models\FlatBill;
use ApartmentApi\Models\FlatBillItem;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Api\Commands\SelectCommand;
use ApartmentApi\Commands\Billing\Config\GetBillConfig;
use ApartmentApi\Commands\Billing\Wrapper\WrapBillConfig;
use ApartmentApi\Models\Billing;


/**
 * Generate Bill
 *
 * @author Mohammed Mudasir
 */
class BillGenerator extends SelectCommand
{
    use InstantiableTrait, ApiResponseTrait;

    protected $societyId;

    protected $shouldSendMail;

    protected $console;

    protected $dispatcher;

    protected $billingRepo;

    protected $billingItemRepo;

    protected $flatRepo;

    protected $bills;

    protected $date;

    protected $billItems;

    protected $billConfigs;

    protected $society;

    protected $flats;

    protected $dummyBillCreated = false;

    protected $errors;

    public function __construct($societyId, Carbon $date, $shouldSendMail = false, $console = null)
    {
        $this->societyId = $societyId;

        $this->date = $date;

        $this->shouldSendMail = $shouldSendMail == 'on' or $shouldSendMail == true ? true: false;

        $this->console = $console;

        $this->errors = new Collection;
    }

    public function handle(
                        BillingRepository $billingRepo,
                        BillingItemRepository $billingItemRepo,
                        SocietyRepository $societyRepo,
                        FlatRepository $flatRepo,
                        Dispatcher $dispatcher)
    {
        $this->dispatcher  = $dispatcher;
        $this->billingRepo = $billingRepo;
        $this->billingItemRepo = $billingItemRepo;
        $this->flatRepo = $flatRepo;
        $this->societyRepo = $societyRepo;
        
        $buildings = $this->getSocietyBuildings();

        // Detach all those bills which are not paid
        $this->dispatcher->dispatch(new Detacher($this->societyId, $this->date, $this->console));

        $flatsBills = FlatsBillsGenerator::instance($this->getBills(), $buildings, $this->date, $this->console);

        if (! $this->dispatcher->dispatch($flatsBills)) {
            // Log error details in log file
            return $this->console->error('Unable to generate bills');
        }

        $attachItems = AttachFlatBillingItems::instance($this->getBillItems(), $this->societyId, $this->date, $this->console);

        if (! $this->dispatcher->dispatch($attachItems)) {
            // Log error details in log file
            return $this->console->error('Unable to attach billing items to flat\'s.');
        }

        if ($this->shouldSendMail) {
            $this->dispatcher->dispatch(new SendFlatBills($this->societyId, $this->date, $this->console));
        }

        $this->console->info('Completed!');

        return true;
    }

    public function getBills()
    {
        $this->bills = $this->bills ?:
            $this   ->buildBillingQuery()
                    ->handleWithMethod('userSociety.building', function($q) {
                        $q->select('id', 'name');
                    })
                    ->handleWithMethod('userSociety.flat', function($q) {
                        $q->select('id', 'flat_no');
                    })
                    ->handleWithMethod('userSociety', function($q) {
                        $q->select('id', 'society_id', 'building_id', 'flat_id')
                          ->where('flat_id', '!=', 'null');
                    })
                    ->withFlats()
                    ->withBuildings()
                    ->get();

        if (! $this->bills->count() > 0) {
            $this->bills = $this->getDefaultConfigBills();
        }

        return $this->bills;
    }

    protected function getDefaultConfigBills()
    {
        $dispatcher = $this->dispatcher;

        $bills = collect();

        $rawBillConfigs = $dispatcher->dispatch(new GetBillConfig($this->societyId));
        $billConfig     = $dispatcher->dispatch(new WrapBillConfig($rawBillConfigs));

        $bill = new Billing(array_merge([
                            'society_id' => $this->societyId,
                            'month' => $this->date,
                            'flats' => collect(),
                            'buildings' => collect(),
                        ], $billConfig->toArray())
                    );

        $bills->push($bill);

        return $bills;
    }

    protected function buildBillingQuery()
    {
        return $this->billingRepo
                    ->where([
                        'society_id' => $this->societyId
                    ])
                    ->whereLike('month', $this->date->format('Y-%m-'));
    }

    protected function buildBullingItemQuery()
    {
        return $this->billingItemRepo
                    ->societyIs($this->societyId)
                    ->withFlats()
                    ->withBuildings()
                    ->getModel()
                    ->where(function($q) {
                        // It should depend upon perticuler month
                        $q->orWhere('month', 'LIKE', $this->date->format('Y-%m-%'))

                        // Or It should be fixed billing item
                          ->orWhere('fixed_billing_item', 'YES');
                    });
    }

    public function getBillItems()
    {
        return $this->buildBullingItemQuery()
                    ->get();
    }

    public function getSocietyBuildings()
    {
        return $this->societyRepo
                    ->fewSelection()
                    ->getBuildings($this->societyId);
    }
}
