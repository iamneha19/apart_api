<?php namespace ApartmentApi\Commands\State;

use ApartmentApi\Commands\Command;

use Illuminate\Contracts\Bus\SelfHandling;
use ApartmentApi\Repositories\StateRepository;
use Api\Traits\JobMutatorTrait;
use Api\Traits\JobApiTrait;
use Api\Traits\JobValidationTrait;
use Illuminate\Http\Request;
use Api\Commands\UpdateCommand;

class UpdateStateCommand extends UpdateCommand
{
    /**
     * Requirment to run command properly
     *
     * @var [type]
     */
    protected $rules = [
        'name' => 'required',
        'id'   => 'required',
    ];

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(StateRepository $repo)
	{
        $state = $repo->find($this->get('id'), true);

		if (! $state)
        {
            return $this->makeErrorResponse('State does not exists.', 400);
        }
        
		if ($repo->exists(['name' => $this->get('name')]) && $state->name !== $this->get('name'))
        {
            return $this->makeErrorResponse($this->get('name') . " state already exists.", 400);
        }

        try {

            return $state->update($this->fields);

        } catch (QueryException $e) {
            return $this->makeErrorResponse('Unable to update state! State does not exists with given id.', 400);
        }
	}
}
