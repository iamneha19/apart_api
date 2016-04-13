<?php namespace ApartmentApi\Http\Controllers\v1;

use ApartmentApi\Http\Requests;
use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Http\Controllers\V1\ApiController;
use ApartmentApi\Commands\BuildingConfig\SaveBuildingConfig;
use ApartmentApi\Commands\BuildingConfig\ShowBuildingConfig;

use Illuminate\Http\Request;

class BuildingConfigController extends ApiController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
            $job = new SaveBuildingConfig($request);
            return $this->dispatch($job);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Request $request)
	{		
            $building_id = $request->get('building_id');
            if($building_id == "undefined") 
            {
                return;
            } else {
                $job = new ShowBuildingConfig($request);
                return $this->dispatch($job);
            }
            
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
