<?php namespace ApartmentApi\Commands\Billing\Generator;

use ApartmentApi\Commands\Command;
use ApartmentApi\Repositories\FlatBillRepository;
use ApartmentApi\Repositories\FlatBillItemRepository;

use Illuminate\Contracts\Bus\SelfHandling;
use DB;
use Carbon\Carbon;

/**
 * Detach bill's and bill item's using society id
 *
 * @author Mohammed Mudasir
 */
class Detacher extends Command implements SelfHandling
{
    protected $societyId;

    protected $console;

    protected $date;

    protected $month;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($societyId, Carbon $date, $console = null)
	{
		$this->societyId = $societyId;

        $this->date = $date;

        $this->month = $date->format('F Y');

        $this->console = $console;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(FlatBillRepository $flatBillRepo, FlatBillItemRepository $flatBillItemRepo)
	{
        DB::transaction(function() use ($flatBillRepo, $flatBillItemRepo) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $this->console->info('Detaching billing Items.');
            $flatBillItemRepo->detachBillItems($this->societyId, $this->date);

            DB::commit();

            $this->console->info('Detaching bills.');
            $flatBillRepo->detachBills($this->societyId, $this->month);
            
            DB::commit();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        });

        return true;
	}

}
