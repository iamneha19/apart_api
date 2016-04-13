<?php namespace ApartmentApi\Commands\City;

use ApartmentApi\Repositories\CityRepository;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Api\Commands\UpdateCommand;

class UpdateCityCommand extends UpdateCommand
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
        $city = $repo->find($this->get('id'), true);

		if (! $city)
        {
            return $this->makeErrorResponse('City does not exists with given id.', 400);
        }

		if ($repo->exists(['name' => $this->get('name')]) && $city->name !== $this->get('name'))
        {
            return $this->makeErrorResponse('City already exists.', 400);
        }

        try {

            return $city->update($this->only('name', 'state_id'));

        } catch (QueryException $e) {
            return $this->makeErrorResponse('Unable to update city! State does not exists with given id.', 400);
        }
	}
}
