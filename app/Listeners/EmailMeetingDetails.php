<?php namespace ApartmentApi\Listeners;

use ApartmentApi\Events\MeetingWasCreated;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Mail\Mailer;
use ApartmentApi\Models\User;

class EmailMeetingDetails {

	protected $mailer;
	
	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct(Mailer $mailer)
	{
		$this->mailer = $mailer;
	}

	/**
	 * Handle the event.
	 *
	 * @param  MeetingWasCreated  $event
	 * @return void
	 */
	public function handle(MeetingWasCreated $event)
	{   
            $invitees = $event->data['invitees'];
            $society_id = $event->data['society_id'];
            $building_id = $event->data['building_id'];
            $meeting_id = $event->data['meeting_id'];
            $roles_name = '';
            switch ($invitees) {
                case 'O':
                    if($building_id){
                      $users = User::getBuildingUsersWithRelation($building_id,'owner');  
                    }else{
                       $users = User::getSocietyUsersWithRelation($society_id,'owner'); 
                    }
                    
                    break;
                case 'M':
                    $users = User::getAttendeesRoleBasedList($meeting_id);
                    $roles_name = User::getRoleNameForMeeting($meeting_id);
                    break;
                case 'A':
                    if($building_id){
                      $users = User::getAllBuildingUsers($building_id);  
                    }else{
                       $users = User::getAllSocietyUsers($society_id);
                    }
                    
                    break;
            }
            foreach($users as $user){
                \Mail::queue('emails.new_meeting_creation',array('meeting'=>$event->data,'role_name'=>$roles_name), function($message) use ($event,$user)
					{ 
					$message->to($user->email, $user->first_name.' '.$user->last_name)->subject($event->data['title']);
					});
            }    
//            $this->mailer->send('emails.welcome_user', ['user'=>$event->data], function($m) use ($event){
//                    $m->to($event->data['email'], $event->data['name'])
//                    ->subject('Welcome to Sahkari');
//            });
	}

}
