<?php

namespace ApartmentApi\Commands\Acl\Extractor;

use Illuminate\Contracts\Bus\SelfHandling;
use DB;

/**
 * Description of PermissionExtractor
 *
 * @author amit.nalawade
 */
class PermissionExtractor implements SelfHandling
{
    protected $moduleAccessBundle;
    protected $societyId;
    protected $userId;

    function __construct($societyId, $userId)
    {
        $this->societyId = $societyId;
        $this->userId = $userId;
    }

    function handle()
    {
        $societyAccessBundle = $this->getSocietyAccessBundle();

        $role_name = $this->IsAdmin();
		
		$buildingAccessBundle = $this->getBuildingAccessBundle();

        $this->moduleAccessBundle = array_merge($societyAccessBundle, $buildingAccessBundle);

        $modules = $this->removeDuplicateModules();

        $permissions = $this->removeDuplicatePermissions($modules);
	    $modulePermissions = $this->separateAdminAndResidentModules($permissions);

		if (count($role_name) > 0){
			$modulePermissions['role_name'] = implode(",", $this->array_flatten($role_name));
		}
		else{
			$modulePermissions['role_name'] = $role_name[0]->role_name ;
		}
                
	   return $modulePermissions;
    }
	
	function array_flatten($array) { 
		if (!is_array($array)) { 
		  return FALSE; 
		} 
		$result = array(); 
		foreach ($array as $key => $value) { 
			$result[$key] = $value->role_name; 
		   
		} 
		return $result; 
	} 
	
    function getSocietyAccessBundle()
    {
        $sql = <<<EOF

        SELECT ar.acl_name AS module,ar.type,ar.title,ar.route,ar.icon,GROUP_CONCAT(DISTINCT arp.permission) permissions FROM acl_role aro
        INNER JOIN acl_role_resource arr ON arr.acl_role_id = aro.id
        INNER JOIN acl_user_role aur ON aur.acl_role_id = arr.acl_role_id
        INNER JOIN acl_resource ar ON ar.acl_name = arr.resource
        LEFT JOIN acl_role_resource_permission arrp ON aro.id = arrp.acl_role_id
        LEFT JOIN acl_resource_permission arp ON arrp.resource_permission_id = arp.id AND ar.acl_name = arp.resource_acl_name
        WHERE aro.society_id = :society_id AND aur.user_id = :user_id  GROUP BY ar.acl_name;

EOF;
	return DB::select($sql,['society_id'=>$this->societyId,'user_id'=>$this->userId]);
    }


    function IsAdmin()
    {
       $sql = '
        SELECT role_name FROM  `acl_role`
		INNER JOIN acl_user_role ON acl_role_id=acl_role.id
		WHERE society_id=:society_id AND acl_user_role.user_id=:user_id ';

        return DB::select($sql,['society_id'=>$this->societyId,'user_id'=>$this->userId]);
    }



    function getBuildingAccessBundle()
    {
       $sql = '
        SELECT ar.acl_name AS module,ar.type,ar.title,ar.route,ar.icon,GROUP_CONCAT(DISTINCT arp.permission) permissions FROM acl_role aro
        INNER JOIN acl_role_resource arr ON arr.acl_role_id = aro.id
        INNER JOIN acl_user_role aur ON aur.acl_role_id = arr.acl_role_id
        INNER JOIN acl_resource ar ON ar.acl_name = arr.resource
        LEFT JOIN acl_role_resource_permission arrp ON aro.id = arrp.acl_role_id
        LEFT JOIN acl_resource_permission arp ON arrp.resource_permission_id = arp.id AND ar.acl_name = arp.resource_acl_name
        WHERE aro.society_id IN (SELECT id FROM society WHERE parent_id = :society_id ) AND aur.user_id = :user_id  GROUP BY ar.acl_name';



    return DB::select($sql,['society_id'=>$this->societyId,'user_id'=>$this->userId]);
    }

    function removeDuplicateModules()
    {
        $modules = array();
      
        foreach ($this->moduleAccessBundle as $key => $moduleAccess) {
            if(!empty($moduleAccess->permissions)){
                if(!empty($modules[$moduleAccess->module]['permissions'])){
                    $modules[$moduleAccess->module]['permissions'] .= ','.$moduleAccess->permissions;
                }else{
                   $modules[$moduleAccess->module]['permissions'] = $moduleAccess->permissions;
                }

            }else{
                if(empty($modules[$moduleAccess->module]['permissions'])){
                    $modules[$moduleAccess->module]['permissions'] = '';
                }

            }

            $modules[$moduleAccess->module]['type'] = $moduleAccess->type;
            $modules[$moduleAccess->module]['title'] = $moduleAccess->title;
            $modules[$moduleAccess->module]['route'] = $moduleAccess->route;
            $modules[$moduleAccess->module]['icon'] = $moduleAccess->icon;

        }

        return $modules;
    }

    function removeDuplicatePermissions($permissions){

        foreach ($permissions as $module => $value)
        {
            if(!empty($value['permissions']))
            {
                $permissions[$module]['permissions'] = array_unique(explode(",", $value['permissions']));
            }
        }

        return $permissions;
    }

    function separateAdminAndResidentModules($permissions){

        $modulePermissions = array('admin' => array_filter($permissions, function($el){
								return $el['type'] == 1;
							}),
							'resident' => array_filter($permissions, function ($el){
								return $el['type'] == 0;
							}));

        return $modulePermissions;
    }
}
