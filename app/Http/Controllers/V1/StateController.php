<?php namespace ApartmentApi\Http\Controllers\V1;

use Illuminate\Http\Request;
use ApartmentApi\Http\Requests\StateRequest;
use ApartmentApi\Repositories\StateRepository;
use ApartmentApi\Commands\State\CreateStateCommand;
use ApartmentApi\Commands\State\UpdateStateCommand;
use ApartmentApi\Commands\State\DeleteStateCommand;
use Api\Presentor;
class StateController extends ApiController
{
    protected $stateRepo;

    public function __construct(StateRepository $stateRepo, Presentor $presentor)
    {
        $this->stateRepo = $stateRepo;

        parent::__construct($presentor);
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
        $queries = collect($request->only(['orderby', 'per_page', 'search']));

        $states = $this->stateRepo->search($queries);

		return $this->presentor->make200Response('List of states.', $states);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(StateRequest $request)
	{
        $job = new CreateStateCommand($request);

        return $this->presentor->defaultJobBehaviour($job);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        $state = $this->stateRepo->fewSelection()->find($id, true);

        if (! $state)
        {
            return $this->presentor->make404Response('State not found');
        }

        return $this->presentor->make200Response('State detail.', $state);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id, StateRequest $request)
	{
        $job = new UpdateStateCommand($id, $request);

        return $this->presentor->defaultJobBehaviour($job);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $job = new DeleteStateCommand($id);

        return $this->presentor->defaultJobBehaviour($job);
	}
        
}
