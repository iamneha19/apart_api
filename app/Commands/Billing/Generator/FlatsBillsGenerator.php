<?php namespace ApartmentApi\Commands\Billing\Generator;

use ApartmentApi\Commands\Command;
use ApartmentApi\Commands\Billing\Generator\FlatBillGenerator;
use ApartmentApi\Console\ProgressBar\BillCounterProgressBar;
use ApartmentApi\Repositories\FlatRepository;
use ApartmentApi\Repositories\FlatBillRepository;
use ApartmentApi\Models\Flat;
use Api\Commands\SelectCommand;
use Illuminate\Support\Collection;
use Illuminate\Bus\Dispatcher;
use Api\Traits\InstantiableTrait;

class FlatsBillsGenerator extends SelectCommand
{
    use InstantiableTrait;

    protected $bills;

    protected $flatRepo;

    protected $date;

    protected $console;

    protected $errors;

    const NOT_SPECIFIED = false;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Collection $bills, Collection $buildings, $date, $console)
	{
		$this->bills = $bills;

        $this->buildings = $buildings;

        $this->date = $date;

        $this->console = $console;

        $this->errors = new Collection;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(Dispatcher $dispatcher, FlatRepository $flatRepo)
	{
        $this->dispatcher = $dispatcher;

        $this->flatRepo   = $flatRepo;

        $this->console->info('Bill compilation started...');

        $progressBar = BillCounterProgressBar::start($this->bills->count());

        $errors = [];

        foreach ($this->bills as $bill) {
            $billableFlats = collect();
            
            // Push all those flats in billable flats list which are specified in bill
            $bill->flats->each(function ($flat) use ($billableFlats) {
                $billableFlats->push($flat);
            });

            // while generating flat specific bills it is require to have buildings or flats in bill
            // if not found then whole society buildings will be targeted
            $buildings = ($this->hasBuildings($bill) or $bill->flats->count() !== 0) ?
                            $bill->buildings:
                            $this->buildings;

            if ($buildings) {
                $this->extractFlats($buildings)
                     ->each(function($flat) use ($billableFlats) {
                         $billableFlats->push($flat);
                     });
            }

            $this->processBillableFlats($billableFlats, $bill);

            $progressBar->advance();
        };

        $progressBar->finish();

        $this->console->info('
        ');

        count($errors) == 0 ?: $this->errors->push($errors);

        return $this->errors->count() == 0;
	}

    public function getErrors()
    {
        return $this->errors;
    }

    protected function hasBuildings($bill)
    {
        return $bill->buildings->count() > 0;
    }

    public function processBillableFlats($billableFlats, $bill)
    {
        $alreadyProcessed = [];
        $billableFlats->unique()->each(function($flat) use ($bill, $alreadyProcessed) {

            if (! in_array($flat->id, $alreadyProcessed)) {
                $this->flatSpecificGenerator($flat, $bill);
                $alreadyProcessed[] = $flat->id;
            }
        });
    }

    public function flatSpecificGenerator($flat, $bill)
    {
        $flatBill = FlatBillGenerator::instance($flat, $bill, $this->date, $this->console);

        if (! $this->dispatcher->dispatch($flatBill)) {
            // Log error
        }

        return true;
    }

    public function extractFlats(Collection $buildings)
    {
        $flats = $this->flatRepo
                    ->fewSelection()
                    ->whereHasBuildings($buildings->unique()->lists('id'))
                    ->withBuilding()
                    ->get();

        return $flats;
    }

}
