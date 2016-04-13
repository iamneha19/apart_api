<?php

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\Event;
use ApartmentApi\Models\ApartmentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use ApartmentApi\Models\OauthToken;

class CalendarController extends Controller {
	public function __construct() {
		$this->middleware ( 'rest' );
	}

	public function storeOrUpdateEvent() {
		// get all posted form data
		$attributes = \Input::all ();
		$name = \Input::get ( 'name' );
		$id = \Input::get ( 'id', null );
		$date = \Input::get ( 'date' );
		$user = OauthToken::find ( \Input::get ( 'access_token' ) )->user ()->first ();
		$society = OauthToken::find ( \Input::get ( 'access_token' ) )->society ()->first ();
		$oauthToken = OauthToken::find ( \Input::get ( 'access_token' ) );
		$user_id = $oauthToken->user_id;
		// $user = OauthToken::find(\Input::get('access_token'))->user()->first();
		// sample validation
		$validator = \Validator::make ( $attributes, array (
				'name' => 'required|min:1',
				'date' => 'required|min:1'
		) );
		if ($validator->fails ())
			return [
					'msg' => 'Input errors',
					'input_errors' => $validator->messages ()
			];
		if ($id) {
			$event = Event::find ( $id );

			if($user_id!=$event->user_id)
			{
				return [
						'success' => false,
						'msg' => 'Cannot change event'
				];
			}

			if (! $event)
				return [
						'success' => false,
						'msg' => 'Event not found'
				];
		}

		else {
			$event = new Event ();
		}
		$event->user ()->associate ( $user );
		$event->society ()->associate ( $society );
		$event->fill ( $attributes );
		$event->save ();
		$event->title = $event->name;
		// $entity->user()->associate($user);

		// Entity::updateOrCreate(['id'=>$id],$attributes);

		return [
				'msg' => $id ? 'Event updated successfully' : 'Event updated successfully',

				'data' => $event, 
				'success' => true,
				'data' => $event,
                'success' => true
		]
		;
	}
	public function getEventList(Request $request) {
		$month = \Input::get ( 'month', null )==0?12:\Input::get ( 'month', null );
		$year = \Input::get ( 'month', null ) == 0 ? \Input::get ( 'year', null ) - 1:\Input::get ( 'year', null );

/* 		if (! $date) {
			$date = date ( "Y-m-d" );
		}

		$d = date_parse_from_format ( "Y-m-d", $date ); */
		//print_r($d ["month"] );
		// getting list by using model
		$results = \DB::select ( '
		 select event.id, event.name as title, event.date
		 from oauth_token inner join event on event.society_id = oauth_token.society_id
		 where oauth_token.token = :access_token
				and YEAR(event.date) = :year
				AND MONTH(event.date) = :month', [
				'access_token' => $request->get ( 'access_token' ),
				'year' => $year,
				'month' => $month
		] );
		/*
		 * $results = \DB::select ( 'select event.id, event.name, event.date
		 * from event where event.date=' . $date . ' AND YEAR(Date) = 2015 AND MONTH(Date) = 9' );
		 */
		/*
		 * $results = \DB::select ( 'select event.id, event.name as title, event.date
		 * from event where YEAR(event.date) = 2015 AND MONTH(event.date) = '.$d["month"].'' );
		 */
		return $results;
	}
	public function deleteEvent($id) {
		$oauthToken = OauthToken::find ( \Input::get ( 'access_token' ) );
		$user_id = $oauthToken->user_id;
		$event = Event::where ( 'id', '=', $id )->where ( 'user_id', '=', $user_id )->first ();

		if ($event) {
			$event->delete ();
			return [
					'msg' => 'Event deleted successfully',
					'success' => true
			];
		} else {
			return [
					'msg' => 'Dont have permission to delete event',
					'success' => false
			];
		}
	}
}
