<?php
namespace ApartmentApi\Commands\Reminder;

use Illuminate\Contracts\Bus\SelfHandling;
use DB;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MeetingReminder
 *
 * @author neha.agrawal
 */
class MeetingReminder implements SelfHandling  {
    //put your code here
    protected $userId;
    protected $societyId;
    
    function __construct($societyId,$userId) 
    {
        $this->userId = $userId;
        $this->societyId = $societyId;
    }
    function handle()
    {
        $meetings = $this->getMeetings();
        
        $myMeetings = $this->getMymeetings($meetings);
        return $myMeetings;
    }
    
    public function getMeetings()
    {
        $meetings = \DB::select("SELECT  meeting.id,meeting.title,meeting.date,meeting.attendees,meeting.title,category.name,reminder.alert FROM meeting 
                                    INNER JOIN category ON meeting.type_id = category.id 
                                    INNER JOIN reminder ON category.id=reminder.type_id
                                   WHERE category.type='meeting' AND (UNIX_TIMESTAMP(meeting.date) - reminder.alert_unix) <= UNIX_TIMESTAMP(NOW()) AND (UNIX_TIMESTAMP(meeting.date) > UNIX_TIMESTAMP(NOW())) and meeting.society_id='".$this->societyId."'");
        
        return $meetings;
    }
    
    public function getMymeetings($meetings)
    {
            $array = [];
        foreach($meetings as $meeting)
        {
            switch($meeting->attendees)
            {
                case "O":
                    $all_members = \DB::select("select count(*) from user_society where relation = 'owner' and society_id='".$this->societyId."' and user_id='".$this->userId."'");
                        if($all_members>0)
                        {
                            $array[$meeting->id]['meeting_id'] = ($meeting->id);
                            $array[$meeting->id]['title'] = ($meeting->title);
                            $array[$meeting->id]['date'] = ($meeting->date);
                        }
                break;
                case "A":
                            $array[$meeting->id]['meeting_id'] = ($meeting->id);
                            $array[$meeting->id]['title'] = ($meeting->title);
                            $array[$meeting->id]['date'] = ($meeting->date);
                break;

                case "M":
                    $role_based_members = \DB::selectOne("SELECT count(acl.user_id) as acl_user_id FROM meeting_attendee AS ma INNER JOIN acl_user_role AS acl ON ma.role_id = acl.acl_role_id WHERE ma.meeting_id='".$meeting->id."' and acl.user_id ='".$this->userId."'");
//                   print_r($meeting->id);
//                    print_r($role_based_members->acl_user_id);exit;
                    if($role_based_members->acl_user_id>0)
                        {
                            $array[$meeting->id]['meeting_id'] = ($meeting->id);
                            $array[$meeting->id]['title'] = ($meeting->title);
                            $array[$meeting->id]['date'] = ($meeting->date);
                        }
                    else{
                        $array = [];
                    }
                    break;
                default:
                    echo"nor owner not all members <br/>";
            }
        }
//        print_r($array);exit;
        return $array;
    }
}
