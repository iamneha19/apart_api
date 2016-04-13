<?php namespace ApartmentApi\Http\Controllers\V1;

use Illuminate\Http\Request;
use ApartmentApi\Http\Requests\CityRequest;
use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Repositories\CityRepository;

use ApartmentApi\Commands\City\CreateCityCommand;
use ApartmentApi\Commands\City\UpdateCityCommand;
use ApartmentApi\Commands\City\DeleteCityCommand;

use Api\Presentor;

class CityController extends ApiController
{
    protected $cityRepo;

    public function __construct(CityRepository $cityRepo, Presentor $presentor)
    {
        $this->cityRepo = $cityRepo;

        parent::__construct();
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
        $presentor = $this->presentor;

        $queries = collect($request->only(['orderby', 'state_id', 'per_page', 'search']));

        $cities = $this->cityRepo->search($queries);

        return count($cities) > 0 ?
                $presentor->make200Response('List of cities.', $cities):
                $presentor->make404Response('No city found.');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(CityRequest $request)
	{
        $job = new CreateCityCommand($request);

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
        $city = $this->cityRepo
                     ->fewSelection()
                     ->findWithState($id, true);

        if (! $city)
        {
            return $this->presentor->make404Response('City not found');
        }

        return $this->presentor->make200Response('City detail.', $city);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id, CityRequest $request)
	{
        $job = new UpdateCityCommand($id, $request);

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
        $job = new DeleteCityCommand($id);

        return $this->presentor->defaultJobBehaviour($job);
	}

}
