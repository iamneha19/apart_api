<?php 

namespace ApartmentApi\Commands\Billing\Generator;

use Api\Commands\SelectCommand;
use ApartmentApi\Repositories\FlatBillItemRepository;
use ApartmentApi\Repositories\FlatRepository;
use ApartmentApi\Repositories\BillingItemRepository;
use ApartmentApi\Console\ProgressBar\BillCounterProgressBar;
use ApartmentApi\Models\Flat;
use ApartmentApi\Models\BillingItem;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Attach Items to flats
 *
 * Note: Need to implement locking system so that once bill is paid then no item will be able to deleted
 * @author Mohammed Mudasir
 */
class AttachFlatBillingItems extends SelectCommand
{
    protected $items;

    protected $societyId;

    protected $flats;

    protected $console;

    protected $flatBillItemRepo;

    protected $errors;

    protected $progressBar;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Collection $items, $societyId, Carbon $date, $console = null)
	{
		$this->items = $items;

        $this->societyId = $societyId;

        $this->date = $date;

        $this->console = $console;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(FlatBillItemRepository $flatBillItemRepo, FlatRepository $flatRepo)
	{
        $this->setter($flatBillItemRepo, $flatRepo);

        $this->console->info('Dealing with billing items, attaching to flats...');

        $this->startProgress()
             ->processItems()
             ->finishProgress();

        $this->console->info('
        ');

        return true;
	}

    protected function startProgress()
    {
        $this->progressBar = BillCounterProgressBar::start($this->items->count());

        return $this;
    }

    protected function finishProgress()
    {
        return $this->progressBar->finish();
    }

    protected function processItems()
    {
        $flats = $this->getSocietyFlats();
        foreach ($this->items as $item) {
            $filteredFlats = $this->userSpecifiedFlats(clone $flats, $item);
            $filteredFlats = $this->userSpecifiedBuildingsFlats($filteredFlats, $item);

            $this->attachFlatsToItem($filteredFlats, $item);
            $this->progressBar->advance();
        }

        return $this;
    }

    /**
     * User specified ids which need to find from flats and keep those id's which matches
     * rest will be deleted
     *
     * @param  Collection $items [description]
     * @param  Collection $flats [description]
     * @return [type]            [description]
     */
    public function userSpecifiedFlats(Collection $flats, BillingItem $item)
    {
        if ($item->flats->count() > 0) {
            $itemFlatIds = $item->flats->lists('id');

            foreach ($flats as $index => $flat) {
                if (! in_array($flat->id, $itemFlatIds)) {
                    $flats->forget($index);
                }
            }
        }

        return $flats;
    }

    public function userSpecifiedBuildingsFlats(Collection $flats, BillingItem $item)
    {
        if ($item->buildings->count() > 0) {
            $item->buildings->each(function($building) use ($flats) {
                $this->flatRepo
                     ->getFlatsByBuildingId($building->id)
                     ->each(function($buildingFlat) use ($flats) {
                         $flats->push($buildingFlat);
                     });
            });
        }

        return $flats;
    }

    public function attachFlatsToItem(Collection $flats, BillingItem $item)
    {
        $flats->each(function($flat) use ($item) {
            // Check user mentioned flat category and bill is unpaid
            if ($this->hasFlatCategory($flat, $item) and $this->hasFlatType($flat, $item) and @$flat->flatBill->status == 'Unpaid') {
                // Attach flat id to item id
                $this->flatBillItemRepo
                     ->syncFlatItem($this->societyId, $flat->id, $item->id, $this->getDate());
            }
        });

        return true;
    }

    protected function hasFlatCategory(Flat $flat, BillingItem $item)
    {
        return  $item->flat_category == $flat->flatDetails->relation or empty($item->flat_category);
    }

    protected function hasFlatType(Flat $flat, BillingItem $item)
    {
        return  $item->flat_type == $flat->type or empty($item->flat_type);
    }

    public function getSocietyFlats()
    {
        // $this->flats ?: is not working as expected, when using $flats on method scope its
        // overwriding on class scope, this is php issue need to report
         return $this->flats ?: $this->flatRepo ->fewSelection()
                                ->hasSociety($this->societyId)
                                ->withFlatBill(function($q) {
                                    $q->select('id', 'flat_id', 'status')
                                      ->where('month', $this->getDate());
                                })
                                ->withUserSociety('id', 'flat_id', 'relation')
                                ->get();
    }


    public function setter(
                        FlatBillItemRepository $flatBillItemRepo,
                        FlatRepository $flatRepo)
    {
        $this->flatBillItemRepo = $flatBillItemRepo;

        $this->flatRepo = $flatRepo;
    }

    public function getDate($format = 'F Y')
    {
        return $this->date->format($format);
    }
}
