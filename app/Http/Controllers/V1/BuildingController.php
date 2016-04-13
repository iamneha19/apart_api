<?php namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Requests;
use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Models\Society;

use Illuminate\Http\Request;

class BuildingController extends ApiController
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
        if (! $societyId = $request->get('society_id')) {
            return $this->presentor()->make400Response('Society id is required');
        }

		$buildings = Society::select('id', 'name as text')->whereParentId($societyId)->get();

        return count($buildings) > 0 ?
                    $this->presentor->make200Response($buildings->count() . ' building\'s found.', $buildings):
                    $this->presentor->make404Response('Society building not found');

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
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
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
