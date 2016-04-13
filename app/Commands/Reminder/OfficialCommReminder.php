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
class OfficialCommReminder implements SelfHandling  {
    //put your code here
    protected $societyId;
    
    function __construct($societyId,$userId) 
    {
        $this->societyId = $societyId;
        $this->userId = $userId;
    }
    function handle()
    {
        $results = $this->getOffCommReminder();
        return $results;
    }
    
    public function getOffCommReminder()
    {
        $role_ids = \DB::selectOne("SELECT GROUP_CONCAT(acl_role.id) AS role_id FROM acl_user_role AS aur INNER JOIN acl_role ON acl_role.id = aur.acl_role_id WHERE acl_role.society_id = '".$this->societyId."' AND aur.user_id = '".$this->userId."'");
        $role_id = $role_ids->role_id; 
            
            $results = \DB::select("SELECT official_communication.id,official_communication.subject, CONCAT(users.first_name,' ',users.last_name) AS user_name,official_communication.is_read
                                    FROM official_communication INNER JOIN users ON official_communication.created_by = users.id
                                    WHERE official_communication.is_read = 0
                                    AND official_communication.recepient_id IN
                                    (
                                        $role_id
                                    )");
            return $results;
    }
}
