<?php namespace ApartmentApi\Commands\State;

use ApartmentApi\Repositories\StateRepository;
use Illuminate\Http\Request;
use Api\Commands\DeleteCommand;

class DeleteStateCommand extends DeleteCommand
{
	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(StateRepository $repo)
	{
		if (! $repo->exists($this->fields))
        {
            return $this->makeErrorResponse('State does not exists', 400);
        }

        return $repo->delete($this->get('id'));
	}
}
