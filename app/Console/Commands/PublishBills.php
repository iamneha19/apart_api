<?php namespace ApartmentApi\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Bus\Dispatcher;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use ApartmentApi\Commands\Billing\Generator\BillGenerator;
use Carbon\Carbon;
use Exception;

class PublishBills extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'publish:bills';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate and send Bills to all flats member of society.';

    protected $format = 'mm-yyyy';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire(Dispatcher $dispatcher)
	{
        // $societyId = $this->ask('Please provide us society id.');
        // $sendMail  = $this->confirm('Should we send published bills to respective user?. [Y|n]');
        //
		// $this->info("Generating bill for given society id $societyId. Please wait...");
        //
        // $date = $this->ask('Please provide us a date by given format ' . $this->format);

        $societyId = $this->argument('society-id');
        $sendMail  = $this->option('send-mail');
        $date = $this->confirmValidDate($this->argument('year-month'));

        $job = BillGenerator::instance($societyId, $date, $sendMail, $this);

        $dispatcher->dispatch($job);
	}

    protected function confirmValidDate($date = null)
    {
        return Carbon::createFromFormat("Y-m", $date);
    }

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['society-id', InputArgument::REQUIRED, 'Society id.'],
			['year-month', InputArgument::REQUIRED, 'Year and month.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['send-mail', null, InputOption::VALUE_OPTIONAL, 'Mail should be send or not.', false],
		];
	}

}
