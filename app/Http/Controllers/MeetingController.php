<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Http\Controllers\Controller;

use Illuminate\Http\Request;
use ApartmentApi\Models\Meeting;
use ApartmentApi\Models\MeetingAttendee;
use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\OauthToken;
use Input;
use Session;

Class MeetingController extends Controller {
    protected $input;
    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(Input $input)
	{
            $this->input = $input;
		$this->middleware('rest',['except'=>['sendAlerts']]);
	}
	 
        // getting list by using model
	public function getMeetingsList() 
        {
//        print_r(session()->get('acl'));exit;
            $society_id = session()->get('acl.society')->id;
           
            // To check permission level society,building
//            if(session()->get('acl.permission.society_id')){
               $where = 'meeting.society_id="'.$society_id.'" and meeting.date>=now()'; 
//            }
//            elseif(session()->get('acl.permission.building_id')){
//                $buildingId = session()->get('acl.permission.building_id');
//               $where = 'meeting.building_id="'.$buildingId.'" and meeting.date>=now()'; 
//            }
//              print_r($where);exit;        
            $search = $this->input->get('search',null);           
            $sort = '';
            $whereSep = '';
            $bindings = array();
            
            if ($this->input->get('search',null)) {
                $where .= ' and (meeting.title like :title or meeting.venue like :venue)';
                $whereSep = true;
                $bindings['title'] = '%'.\Input::get('search').'%';
                $bindings['venue'] = '%'.\Input::get('search').'%';
            }
            $where = $where ? ' where '.$where : '';
            
            if (\Input::get('sort',null)){
                $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','desc').' ';
            }
            $results = \DB::select("select meeting.*,users.first_name as user_name,"
                                . " date_format(meeting.date,'%d-%m-%Y,%h-%i %p') as date, date_format(meeting.created_at,'%d-%m-%Y,%h-%i%p') as created_at from meeting "
                                . " INNER JOIN users ON meeting.user_id=users.id $where $sort limit :limit offset :offset",
                                    array_merge($bindings,array(
                                    'limit'=>\Input::get('limit',5),
                                    'offset'=>\Input::get('offset',0)
                                    )
                            )
                        );
//            print_r($results);exit;
            $count = \DB::selectOne(
				"select count(meeting.id) total from meeting
				 $where ",
					$bindings
				);        
		return ['total'=>$count->total,'data'=>$results];
		
	}
	
	public function getMeeting($id) {
		$arr=[];
		$post = \DB::selectOne('select meeting.*,users.first_name as user_name from meeting INNER JOIN users ON meeting.user_id=users.id where meeting.id='.$id.' limit 1');
//		print_r($post);exit;
//        $post = \DB::selectOne('select meeting.*,meeting_attendee.*,users.first_name as user_name from meeting INNER JOIN users ON meeting.user_id=users.id INNER JOIN meeting_attendee ON meeting.id = meeting_attendee.meeting_id where meeting.id='.$id.' limit 1');
		$attendees = \DB::select('select meeting_attendee.role_id from meeting_attendee INNER JOIN meeting ON meeting.id = meeting_attendee.meeting_id where meeting.id='.$id);
        foreach($attendees as $attendee)
        {
            $arr[] = (int)$attendee->role_id;
        }
//        print_r($arr);exit;
        if (!$post)
			return ['msg'=>'Meeting doesnot exist with id - '.$id];
		
		return ['post'=>$post,'role_ids'=>$arr];
	}
//        public function updateOrCreate($id = null) {
//		
//		// get all posted form data
//		$attributes = \Input::all();
////                print_r($attributes);exit;
//		$user = OauthToken::find(\Input::get('access_token'))->user()->first();
//		$attributes['user_id'] = $user->id;
//		// print_r($attributes);
//		// sample validation
//		$validator = \Validator::make(
//				$attributes,
//				array('title' => 'required','venue'=>'required')
//                               
//		);
//		if ($validator->fails())
//			return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
//		
//                Meeting::updateOrCreate(['id'=>$id],$attributes);
//		if($id==null)
//                {
//                    return ['msg'=>' Meeting created successfully'];
//                }else{
//                    
//                    return ['msg'=>'Meeting updated successfully'];
//                }
//		}
        public function create(Request $request)
        {

            $society = session()->get('acl.society');
            $society_id = session()->get('acl.society')->id;
            $building_id = NULL;
            $user = session()->get('acl.user');
            $attributes = $request->all();
			
			// User has both (Society,Building) wise create permission 
            if($request->has('level'))
            {
                $level = $request->get('level');
                // If selected building level
                if($level == 'building'){
                    $building_id = session()->get('acl.permission.building_id');
                    $attributes['building_id'] = $building_id;
                }
                
            }else{
                // If user has only building wise permission
                if(session()->get('acl.permission.building_id')){
                    $building_id = session()->get('acl.permission.building_id');
                    $attributes['building_id'] = $building_id;
                 }
            }
            
//            dd($attributes);
//            if($attributes['attendees']=='M')
//            {
//                print_r($attributes['role_id']);exit;
//            }
//            $attributes['society_id'] = $society_id;
            $validator = \Validator::make(
                    $attributes,
                    array(
                        'title' => 'required',
                        'venue'=>'required',
//                        'type_id'=>'required'
                        )
                );
            if ($validator->fails())
                return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];
            $meeting = new Meeting();
            $meeting->user()->associate($user);
            $meeting->society()->associate($society);
            $meeting->fill($attributes);
            $meeting->save();
//            print_r($)
//            $meeting_attendee_role_id = implode(',', $attributes['role_id']);
//            print_r($meeting_attendee_role_id);exit;
//            if(is_array($attributes['role_id']))
//            {
                if(isset($attributes['role_id']))
                {
                    foreach ($attributes['role_id'] as $roles_id){

                        $role = AclRole::find($roles_id);
                        $meeting_attendee = new MeetingAttendee();
                        $meeting_attendee->meeting()->associate($meeting);
                        $meeting_attendee->role()->associate($role);
                        $meeting_attendee->save();
                    }
                }
//                $meeting_attendee = $post = \DB::select("select meeting_attendee.role_id, acl_role.role_name from meeting_attendee INNER JOIN acl_role ON meeting_attendee.role_id = acl_role.id where meeting_attendee.meeting_id=".$meeting->id);
//                print_r($meeting_attendee);exit;
//            }
//            print_r($subject);
//            $meeting_attendee = new MeetingAttendee();
//            $meeting_attendee->meeting_id = $meeting->id;
//            $meeting_attendee->fill($attributes);
//            $meeting_attendee->role_id = implode(',', $attributes['role_id']);
//            print_r($meeting_attendee);exit;
//            $meeting_attendee->save();
            
            $meetingData = array(
                            'meeting_id'=>$meeting->id,
                            'title'=>$meeting->title,
                            'created_by'=>$meeting->user()->first()->first_name,
                            'venue'=>$meeting->venue,
                            'notes'=>$meeting->description,
                            'agenda'=>$meeting->agenda,
                            'meeting_on'=>date('j M Y h:i A',strtotime($meeting->date)),
                            'invitees'=>$meeting->attendees,
                            'society_id'=>$society_id,
                            'building_id'=>$building_id
                        );
            event(new \ApartmentApi\Events\MeetingWasCreated($meetingData));
            return ['msg'=>'Meeting created successfully!','success'=>true];
        }
        
        public function edit($id)
        {
            
            $society_id = session()->get('acl.society')->id;
            
           
            $attributes = \Input::all();
//            print_r($attributes);exit;
            $meeting_details_changed = false; // To check timing or attendees changed
            $validator = \Validator::make(
                $attributes,
                array('title' => 'required','venue'=>'required')
            );
            if ($validator->fails())
                return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
                $meeting = Meeting::find($id);
                if(!$meeting)
                {
                    return ['msg'=>'Meeting doesnot exist with id - '.$id];
                }
                if(($meeting->date != $attributes['date']) || ($meeting->attendees != $attributes['attendees'])){
                    $meeting_details_changed = true;
                }
                $meeting->fill($attributes);
                $meeting->save();
                
                if(isset($attributes['role_id']))
                {                    
                    MeetingAttendee::where('meeting_id','=',$id)->delete();
                    foreach ($attributes['role_id'] as $roles_id){

                        $role = AclRole::find($roles_id);
                        $meeting_attendee = new MeetingAttendee();
                        $meeting_attendee->meeting()->associate($meeting);
                        $meeting_attendee->role()->associate($role);
                        $meeting_attendee->save();
                    }
                    
                }
                
                if($meeting_details_changed){
                    $meetingData = array(
                            'meeting_id'=>$id,
                            'title'=>$meeting->title,
                            'created_by'=>$meeting->user()->first()->first_name,
                            'venue'=>$meeting->venue,
                            'notes'=>$meeting->description,
                            'agenda'=>$meeting->agenda,
                            'meeting_on'=>date('j M Y h:i A',strtotime($meeting->date)),
                            'invitees'=>$meeting->attendees,
                            'society_id'=>$society_id,
                            'building_id'=>$meeting->building_id
                        );
                    event(new \ApartmentApi\Events\MeetingWasCreated($meetingData));
                }
                
                return ['msg'=>'Meeting updated successfully!','success'=>true];
            
        }
        public function delete($id) {
		
		// for completely removing entity from database
		//Entity::destroy($ids);
		
		// for soft deleting
		$post = Meeting::find($id);
                if($post)
                {
                   //print_r($post);die;
                    $post->delete();
                    return ['msg'=>'Meeting deleted successfully'];
                }else{
                    
                     return ['msg'=>'No records found'];
                }
	}
        public function restore($id) {
		
		ApartmentUser::withTrashed()->where('id','=',Input::get('apartment_user_id'))->restore();
		
		return ['msg'=>'Meeting restored successfully'];
	}
        
        public function oldMeeting()
        {
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            
            $search = $this->input->get('search',null);
            $where = 'meeting.society_id="'.$society_id.'" and meeting.date < now()';
            $sort = '';
            $whereSep = '';
            $bindings = array();
            
            if ($this->input->get('search',null)) {
                $where .= ' and (meeting.title like :title or meeting.venue like :venue)';
                $whereSep = true;
                $bindings['title'] = '%'.\Input::get('search').'%';
                $bindings['venue'] = '%'.\Input::get('search').'%';
            }
            $where = $where ? ' where '.$where : '';
            
            if (\Input::get('sort',null)){
                $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','desc').' ';
            }
            $results = \DB::select("select meeting.*,users.first_name as user_name,date_format(meeting.date,'%d-%m-%Y, %h-%m-%s') as date from meeting INNER JOIN users ON meeting.user_id=users.id $where $sort limit :limit offset :offset",
                            array_merge($bindings,array(
                                    'limit'=>\Input::get('limit',2),
                                    'offset'=>\Input::get('offset',0)
                                    )
                            )
                        );
            $count = \DB::selectOne(
				"select count(meeting.id) total from meeting
				 $where ",
					$bindings
				);        
		return ['total'=>$count,'data'=>$results];
		
        }
        
        public function sendAlerts()
        {
           $meetings =  Meeting::where('date', '>', date('Y-m-d H:i:s', time()))->where('date', '<', date('Y-m-d H:i:s', strtotime('+1 week')));
           
           foreach($meetings as $meeting){
               $meeting_time = strtotime($meeting->date);
               $meetingData = array(
                            'title'=>$meeting->title,
                            'created_by'=>$meeting->user()->first()->first_name,
                            'venue'=>$meeting->venue,
                            'notes'=>$meeting->description,
                            'agenda'=>$meeting->agenda,
                            'meeting_on'=>date('j M Y h:i A',strtotime($meeting->date)),
                            'invitees'=>$meeting->attendees,
                            'society_id'=>$meeting->society_id
                        );
               if($meeting->hour){
                   if($meeting_time = strtotime('+1 hour')){
                       event(new \ApartmentApi\Events\MeetingWasCreated($meetingData));
                   }
               }
               
               if($meeting->day){
                   if($meeting_time = strtotime('+1 day')){
                      event(new \ApartmentApi\Events\MeetingWasCreated($meetingData)); 
                   }
               }
               
               if($meeting->week){
                   if($meeting_time=strtotime('+1 week')){
                      event(new \ApartmentApi\Events\MeetingWasCreated($meetingData)); 
                   }
               }
           }
           return json_encode(array('msg'=>'Successfully sent alerts','success'=>true));
           
        }
        
        public function SendManualInvitees($meeting_id)
        {
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            
            $meeting_data = \DB::selectOne('select meeting.*,users.first_name as user_name from meeting INNER JOIN users ON meeting.user_id=users.id where meeting.id='.$meeting_id.' limit 1');
            
            $meetingData = array(
                            'meeting_id'=>$meeting_id,
                            'title'=>$meeting_data->title,
                            'created_by'=>$meeting_data->user_name,
                            'venue'=>$meeting_data->venue,
                            'notes'=>$meeting_data->description,
                            'agenda'=>$meeting_data->agenda,
                            'meeting_on'=>date('j M Y h:i A',strtotime($meeting_data->date)),
                            'invitees'=>$meeting_data->attendees,
                            'society_id'=>$society_id,
							'building_id'=>NULL
                        );
                    event(new \ApartmentApi\Events\MeetingWasCreated($meetingData));
                    
            if(!$meeting_data)
            {
                return['success'=>'false','msg'=>'Meeting does not exist with'.$meeting_id];
            }else{
                return['success'=>'true','msg'=>'Invitation send successfully!'];
            }
            
        }
}
