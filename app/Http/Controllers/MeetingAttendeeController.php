<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\MeetingAttendee;

Class MeetingAttendeeController extends Controller {
    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('rest');
	}
	
	public function getAttendeesList() {
		
		// getting list by using model
               //  $results = MeetingAttendee::all(); // columns to retreive
                $results = \DB::select('select meeting_attendee.*,users.name,meeting.title from meeting_attendee INNER JOIN users ON meeting_attendee.user_id = users.id INNER JOIN meeting ON meeting_attendee.meeting_id = meeting.id');
		
		// getting list By using plain query
		//$results = DB::select('select * from post limit 15 order by created_at desc');
		
		//return response()->json(['testdata'=>'test data returned from list action']);
               // print_r($results);exit;
		return $results;
		
	}
	
	public function getAttendee($id) {
		
		  $post = \DB::select('select meeting_attendee.*,users.name,meeting.title from meeting_attendee INNER JOIN users ON meeting_attendee.user_id = users.id INNER JOIN meeting ON meeting_attendee.meeting_id = meeting.id where meeting_attendee.id='.$id);
		
		if (!$post)
			return ['msg'=>'Attendee doesnot exist with id - '.$id];
		
		return $post;
	}
        /// used for both update and delete action
        public function updateOrCreate($id = null) {
		
		// get all posted form data
		$attributes = \Input::all();
		
		// sample validation
		$validator = \Validator::make(
				$attributes,
				array('meeting_id' => 'required')
                               
		);
		
		if ($validator->fails())
			return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
                $attributes['user_id'] = 1;
                MeetingAttendee::updateOrCreate(['id'=>$id],$attributes);
		if($id==null)
                {
                    return ['msg'=>'Attendees created successfully'];
                }else{
                    
                    return ['msg'=>'Attendees updated successfully'];
                }
	}
        
         public function delete($id) {
		
		// for completely removing entity from database
		//Entity::destroy($ids);
		
		// for soft deleting
		$post = MeetingAttendee::find($id);
                if($post)
                {
                   //print_r($post);die;
                    $post->delete();
                    return ['msg'=>'Meeting Attendees deleted successfully'];
                }else{
                    
                     return ['msg'=>'No records found'];
                }
	}
}