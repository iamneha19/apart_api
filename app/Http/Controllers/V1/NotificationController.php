<?php namespace ApartmentApi\Http\Controllers\v1;

use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Http\Requests;
use ApartmentApi\Commands\Notification\ListChaimanNotification;
use Illuminate\Http\Request;

/*
 *	Notification related CRUD methods. 
 * 
 *	@category	Wings Configuration
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 *	@deprecated	File deprecated in Release 2.2.1		
 * 
 */

class NotificationController extends ApiController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$societyId = OauthToken::find($request->get('access_token'))->society()->first()->id;
		$command = new ListChaimanNotification($societyId);
		
		$notification = $this->dispatch($command);
		
		return $notification ?
            $this->presentor()->make200Response($command->getMessage(), $notification):
            $this->presentor()->makeResponseByCode($command->getError(), $command->getStatusCode());
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
