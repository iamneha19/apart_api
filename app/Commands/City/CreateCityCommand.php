<?php namespace ApartmentApi\Commands\City;

use Api\Commands\CreateCommand;

use ApartmentApi\Repositories\CityRepository;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class CreateCityCommand extends CreateCommand
{
    /**
     * Requirment to run command properly
     *
     * @var [type]
     */
    protected $rules = [
        'name' => 'required',
        'state_id' => 'required|integer'
    ];

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(CityRepository $repo)
	{
		if ($repo->exists($this->fields))
        {
            return $this->makeErrorResponse('City already exists or selected state does not exists.', 400);
        }

        try {
            return $repo->create($this->fields);
        } catch (QueryException $e) {
            return $this->makeErrorResponse('Unable to create city! State does not exists with given id.', 400);
        }
	}

}
