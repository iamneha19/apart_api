<?php
namespace ApartmentApi\Commands\Acl\Extractor;

use Illuminate\Contracts\Bus\SelfHandling;
use DB;
use Session;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SocitiesExtractor
 *
 * @author amit.nalawade
 */
class SocietiesExtractor implements SelfHandling
{
    protected $userId;

    function __construct($userId)
    {
        $this->userId = $userId;
    }

    function handle()
    {

        $societies = $this->getSocietiesWithSocietyRoles();
        
        $buildings = $this->getSocietiesWithBuildingRoles();

       $result =  array_merge_recursive($societies,$buildings);

        $result1 = $this->array_unique_multidimensional($result);
       return $result1;
    }

    function array_unique_multidimensional($input)
    {
        $serialized = array_map('serialize', $input);
        $unique = array_unique($serialized);
        return array_intersect_key($input, $unique);
    }


    function getSocietiesWithSocietyRoles()
    {


        $sql = <<<EOF

		SELECT DISTINCT society.id,society.name,is_approved,acl_role.role_name FROM user_society
        INNER JOIN society ON society.id = user_society.society_id
		left JOIN society_config ON society.id = society_config.society_id	
        INNER JOIN acl_role ON acl_role.society_id = user_society.society_id
        INNER JOIN acl_user_role ON acl_user_role.acl_role_id = acl_role.id AND acl_user_role.user_id = user_society.user_id
        WHERE user_society.user_id = :user_id AND user_society.status = 1        
        ORDER BY society.name ASC

EOF;
        $users=DB::select($sql,['user_id'=>$this->userId]) ;
        foreach($users as $user){            
            if(isset($user->role_name) && strtolower($user->role_name)=="chairperson"  || strtolower($user->role_name)=="chairman")
		  		return DB::select($sql,['user_id'=>$this->userId]);
        }       
		if(isset($users[0]->role_name) && strtolower($users[0]->role_name)=="admin")
		  		return DB::select($sql,['user_id'=>$this->userId]);
		else{

		    $sql = <<<EOF

		SELECT DISTINCT society.id,society.name,is_approved,acl_role.role_name FROM user_society
        INNER JOIN society ON society.id = user_society.society_id
        LEFT JOIN society_config ON society.id = society_config.society_id	
        INNER JOIN acl_role ON acl_role.society_id = user_society.society_id
        INNER JOIN acl_user_role ON acl_user_role.acl_role_id = acl_role.id AND acl_user_role.user_id = user_society.user_id
        WHERE user_society.user_id = :user_id AND user_society.status = 1
        GROUP BY society.id
        ORDER BY society.name ASC

EOF;
				  		$users=DB::select($sql,['user_id'=>$this->userId]) ;
        
		if(isset($users[0]->role_name) && (strtolower($users[0]->role_name)=="chairman" || strtolower($users[0]->role_name)=="chairperson" ))
		  		return DB::select($sql,['user_id'=>$this->userId]);
                              else {
                               $sql = ('SELECT DISTINCT society.id,society.name,is_approved,acl_role.role_name FROM user_society
        INNER JOIN society ON society.id = user_society.society_id
		LEFT JOIN society_config ON society.id = society_config.society_id	
        INNER JOIN acl_role ON acl_role.society_id = user_society.society_id
        INNER JOIN acl_user_role ON acl_user_role.acl_role_id = acl_role.id AND acl_user_role.user_id = user_society.user_id
        WHERE user_society.user_id = :user_id AND user_society.status = 1 and user_society.flat_id is not Null
        GROUP BY society.id
        ORDER BY society.name ASC');                             
                               return DB::select($sql,['user_id'=>$this->userId]);
                              }
		}
              

    }

    function getSocietiesWithBuildingRoles()
    {
        $sql = <<<EOF

		SELECT DISTINCT society.id,society.name FROM user_society
        INNER JOIN society ON society.id = user_society.society_id
        INNER JOIN acl_role ON acl_role.society_id = user_society.building_id
        INNER JOIN acl_user_role ON acl_user_role.acl_role_id = acl_role.id AND acl_user_role.user_id = user_society.user_id
        WHERE user_society.user_id = :user_id AND user_society.status = 1 ORDER BY society.name ASC

EOF;
		return DB::select($sql,['user_id'=>$this->userId]);
    }
}
