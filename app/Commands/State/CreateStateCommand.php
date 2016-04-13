<?php namespace ApartmentApi\Commands\State;

use ApartmentApi\Repositories\StateRepository;
use Illuminate\Http\Request;
use Api\Commands\CreateCommand;

class CreateStateCommand extends CreateCommand
{
    /**
     * Requirment to run command properly
     *
     * @var [type]
     */
    protected $rules = [
        'name' => 'required',
    ];

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(StateRepository $repo)
	{
		if ($repo->exists($this->fields))
        {
            return $this->makeErrorResponse('State already exists.', 400);
        }

        return $repo->create($this->fields);
	}

}
