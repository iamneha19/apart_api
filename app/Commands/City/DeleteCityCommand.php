<?php namespace ApartmentApi\Commands\City;

use Api\Commands\DeleteCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use ApartmentApi\Repositories\CityRepository;
use Api\Traits\JobMutatorTrait;
use Api\Traits\JobApiTrait;
use Api\Traits\JobValidationTrait;
use Illuminate\Http\Request;

class DeleteCityCommand extends DeleteCommand
{
	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(CityRepository $repo)
	{
		if (! $repo->exists($this->fields))
        {
            return $this->makeErrorResponse('City does not exists', 400);
        }

        return $repo->delete($this->get('id'));
	}
}
