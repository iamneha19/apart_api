<?php
namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Commands\Acl\AddAclUserRole;
use ApartmentApi\Commands\Acl\AddRoleModuleAccess;
use ApartmentApi\Commands\Acl\AddRoleModulePermission;
use ApartmentApi\Commands\Acl\DeleteRoleModuleAccess;
use ApartmentApi\Commands\Acl\DeleteRoleModulePermission;
use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Models\AclResource;
use ApartmentApi\Models\AclResourcePermission;
use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\AclUserResource;
use ApartmentApi\Models\AclUserResourcePermission;
use ApartmentApi\Models\AclUserRole;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\Society;
use ApartmentApi\Models\User;
use DB;
use Illuminate\Http\Request;
use SebastianBergmann\RecursionContext\Exception;

class AclController extends Controller
{
	
	public function __construct() {
		
		$this->middleware('rest');
	}
	
	public function resourceList(AclResource $resource) {
		
		$resources =  DB::select('
					select acl_resource.acl_name,group_concat(arp.permission) permissions
					from acl_resource left join acl_resource_permission arp
					on arp.resource_acl_name = acl_resource.acl_name
					group by acl_resource.acl_name order by acl_name asc');
		
		foreach ($resources as $k=>$res) {
			$resources[$k]->permissions = explode(',',$resources[$k]->permissions);
                }
		return $resources;
               
	}
	
	public function addResource(Request $request) {
		$resource = new AclResource();
		$resource->acl_name = $request->get('acl_name');
		$resource->save();
                $resource->permissions=[];
		return ['success'=>true,'msg'=>'Resource added to acl','resource'=> $resource ];	
	}
        
	public function addOrUpdateUserPermission(Request $request, OauthToken $token) {
		
		$user = $token->find($request->get('access_token'))->society()->first();
                $oauthToken = OauthToken::find($request->get('access_token'));
		$society = $oauthToken->society()->first();
		$user = User::find($request->get('user_id'));
		$resource = AclResource::find($request->get('resource'));
		$permission = AclResourcePermission::where('resource_acl_name',$request->get('resource'))->first();
		
		if ($request->get('permitted')) {
		$userResourcePermission = new AclUserResourcePermission();
		$userResourcePermission->permission()->associate($permission);
		$userResourcePermission->user()->associate($user);
                $userResourcePermission->society()->associate($society);
		$userResourcePermission->save();
		} else {
			
			$sql = 'delete aurp from acl_user_resource_permission aurp 
					inner join acl_resource_permission arp on arp.id = aurp.resource_permission_id
					where arp.resource_acl_name = :resource and aurp.user_id = :user_id';
			
			DB::delete($sql,['resource'=>$request->get('resource'),'user_id'=>$request->get('user_id')]);
		}
		return ['success'=>true,'msg'=>'permission updated successfully'];
		
	}
	
	public function editRoleName(Request $request){

		$society_id = OauthToken::find($request->get('access_token'))->society_id;
		
        $validator = \Validator::make(
                $request->all(),
                array(  
                    'role_name' => 'required',
                    'role_id'=>'required',
                    )  
                );
                
        if ($validator->fails())
            return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];
        
		$role = AclRole::where('society_id','=',$society_id)
		->where('role_name','=',$request->get('role_name'))->where('id','!=',$request->get('role_id'))->first();
		if ($role) 
			return ['success'=>false,'msg'=>'Role \''.$request->get('role_name').'\' already exists.'];
		
 		$role = AclRole::where('id', $request->get('role_id'))->first();
		$role->role_name = $request->get('role_name');
		$role->save();
        
 		return ['msg'=>'Successfully changed role name','data'=>$role,'success'=>true];
		
	}
	
	
	

        
    public function addOrUpdateRoleModuleAccess(Request $request) {
        $roleId = $request->get('role_id');
        $resourceId = $request->get('resource');

        try {
            $resource = AclResource::findOrFail($resourceId);
        }  catch (Exception $e) {
            return [
                'success' => false,
                'msg'=> $e->getMessage()
            ];
        }

        try {
            $aclRole = AclRole::findOrFail($roleId);
        }  catch (Exception $e) {
            return [
                'success' => false,
                'msg'=> $e->getMessage()
            ];
        }

        if ($request->get('permitted')) {

            $command = new AddRoleModuleAccess($request, $aclRole, $resource);

            $this->dispatch($command);


        } else {
           $command =  new DeleteRoleModuleAccess($request, $roleId, $resourceId);

           $this->dispatch($command);

        }

        return ['success'=>true,'msg'=>'Role module access updated!'];
    }
        
    public function addOrUpdateRoleModulePermission(Request $request) {
        $roleId = $request->get('role_id');
        $permissionId = $request->get('permission_id');

        try {
            $permission = AclResourcePermission::findOrFail($permissionId);
        }  catch (Exception $e) {
            return [
                'success' => false,
                'msg'=> $e->getMessage()
            ];
        }

        try {
            $aclRole = AclRole::findOrFail($roleId);
        }  catch (Exception $e) {
            return [
                'success' => false,
                'msg'=> $e->getMessage()
            ];
        }


        if ($request->get('permitted')) {

            $command = new AddRoleModulePermission($request, $permission, $aclRole);

            $this->dispatch($command);
        } else {
            $command = new DeleteRoleModulePermission($request, $roleId, $permissionId);

            $this->dispatch($command);
        }


        return ['success'=>true,'msg'=>'Role permission updated!'];
    }
        
    public function addOrUpdateAclUserRole(Request $request) {
        $societyId = OauthToken::find($request->get('access_token'))->society_id;
        $roleId = $request->get('role_id');
        $userId = $request->get('user_id');

        if($request->has('building_id')){
            $buildingId = $request->get('building_id');

            $buildings =  DB::selectOne('select count(id) as count from acl_role join acl_user_role on acl_role.id = acl_user_role.acl_role_id where society_id IN (select society.id from society where parent_id = :society_id and society.id != :building_id) and acl_user_role.user_id = :user_id',array('society_id'=>$societyId, 'building_id'=>$buildingId, 'user_id'=>$userId));
            if($buildings->count){
               return ['success'=>false,'msg'=>'User has roles from other buildings also. Please assign roles from one buliding only. ']; 
            }
        }



        $user = User::find($userId);
        $aclRole = AclRole::find($roleId);
        $child_roles = AclRole::where('parent_id','=',$roleId)->get();
        if ($request->get('permitted')) { 
            if($aclRole->is_unique){
               $role = AclUserRole::where('acl_role_id','=',$roleId)->first();
               if($role){
                   if($aclRole->role_name == 'Admin' ){
                        AclUserRole::where('acl_role_id','=',$roleId)->delete();


                        $old_child_roles = AclRole::where('parent_id','=',$roleId)->get();

                        foreach($old_child_roles as $child_role ){
                            DB::table('acl_user_role')->where('user_id','=',$role->user_id)
                        ->where('acl_role_id','=',$child_role->id)->delete();
                        }

                        $command = new AddAclUserRole($request, $user, $aclRole);
                        $this->dispatch($command);


                        return ['success'=>true,'msg'=>'Role permission updated!'];
                   }else{
                       return ['success'=>false,'msg'=>'Role is already assigned to another user. Please remove that!'];
                   }

               }else{

                    $command = new AddAclUserRole($request, $user, $aclRole);
                    $this->dispatch($command);

                    foreach($child_roles as $child_role ){
                        $aclUserRole = new AclUserRole();
                        $aclUserRole->user()->associate($user);
                        $aclUserRole->acl_role_id = $child_role->id;
                        $aclUserRole->save();
                    }

                    return ['success'=>true,'msg'=>'Role permission updated!'];
               }
            }else{

                    $command = new AddAclUserRole($request, $user, $aclRole);
                    $this->dispatch($command);


                    return ['success'=>true,'msg'=>'Role permission updated!'];
               }

        } else {
            DB::table('acl_user_role')->where('user_id','=',$userId)
            ->where('acl_role_id','=',$roleId)->delete();

            foreach($child_roles as $child_role ){

                DB::table('acl_user_role')->where('user_id','=',$userId)
            ->where('acl_role_id','=',$child_role->id)->delete();
            }


            return ['success'=>true,'msg'=>'Role permission updated!'];
        }


    }
	
    public function addPermissionResource (Request $request) {
       $permission = new AclResourcePermission();
       $aclResource = AclResource::find($request->get('acl_name'));
       $permission->permission = $request->get('permission');
       $permission = $aclResource->permissions()->save($permission);


        return ['success'=>true,'msg'=>'permission added to Resource','permission'=> $permission ];
    }
	
    public function userList(Request $request) {


        $society_id  = OauthToken::find($request->get('access_token'))->society_id;
        if($request->has('building_id')){
            $society_id1  = $request->get('building_id');
            $where = 'us.building_id = :society_id1';
        }else{
            $society_id1  = $society_id;
            $where = 'us.society_id = :society_id1';
        }
        $sort = '';

        if (\Input::get('sort',null)){
           $sort =  \Input::get('sort',null);
            if($sort == 'user'){
                $sort = ' order by users.id '.\Input::get('sort_order','asc').' '; 
            }else{
               $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','asc').' '; 
            }
            
        }
        
//        $sql = <<<EOF
//
//            select users.id,concat(users.first_name,' ',users.last_name) name,users.email,admin.role_name 
//            from users inner join user_society us on us.user_id = users.id
//                
//            left join
//            (
//             select aur.user_id,acl_role.society_id,acl_role.role_name from acl_user_role aur 
//                inner join acl_role on acl_role.id = aur.acl_role_id
//             where acl_role.role_name = 'admin'
//            ) as admin on admin.user_id = users.id and admin.society_id = :society_id
//
//            where $where and us.status = 1 group by users.id $sort		
//EOF;
		 $sql = <<<EOF
            select users.id,CONCAT(users.first_name,' ',users.last_name) name,users.email,GROUP_CONCAT(DISTINCT admin.role_name) AS role_name 
            FROM users inner join user_society us ON us.user_id = users.id
                
            inner join
            (
             SELECT aur.user_id,acl_role.society_id,acl_role.role_name,aur.acl_role_id FROM acl_user_role aur 
                inner join acl_role ON acl_role.id = aur.acl_role_id
            ) as admin on admin.user_id = users.id and admin.society_id = :society_id
                 
                where $where and us.status = 1 group by users.id $sort
EOF;
            return DB::select($sql,['society_id'=>$society_id,'society_id1'=>$society_id1]);
    }
	
	
    public function getUserPermissions(Request $request) {

        $sql = <<<EOF

        select ar.acl_name,
        concat('[',group_concat(concat('{"permission":"',arp.permission,'","permitted":',ifnull(user_permission.permitted,0),'}')),']') permissions
        from acl_resource ar 
        inner join acl_resource_permission arp on arp.resource_acl_name = ar.acl_name
        left join (
            select count(aurp.user_id) permitted,aurp.resource_permission_id from oauth_token
            inner join user_society on user_society.society_id = oauth_token.society_id
            inner join users on users.id = user_society.user_id
            inner join acl_user_resource_permission aurp on aurp.user_id = users.id
            where oauth_token.token = :access_token and users.id = :user_id and aurp.society_id = oauth_token.society_id
            group by aurp.resource_permission_id
        ) as user_permission on user_permission.resource_permission_id = arp.id
        group by ar.acl_name		
EOF;
		
        $results = DB::select($sql,
                [
                        'access_token'=>$request->get('access_token'),
                        'user_id'=>$request->get('user_id')
                ]
                );

        foreach($results as $key=>$value) {
            $results[$key]->permissions = json_decode($results[$key]->permissions);
        }

        return $results;

    }
    
    /*
     * Get all permissions of given module with option checked(0,1) for given role_id
     * @type  0:Both, 1:Society only, 2:Building only
     */
    public function getRoleModulePermissions(Request $request) {
        if($request->has('building_id')){
           $where =  'acl_resource_permission.resource_acl_name = :resource and acl_resource_permission.type != 1'; // Don't show only society level permissions like Society Document List and Upload
        }else{
          $where =   'acl_resource_permission.resource_acl_name = :resource and acl_resource_permission.type != 2'; // Don't show only building level permissions like My Building Access Control  
        }
        $sql = "SELECT acl_resource_permission.id,acl_resource_permission.permission,acl_resource_permission.title,ifnull(permitted.permitted,0) as permitted FROM acl_resource_permission
                LEFT JOIN ( 
                    SELECT arr.resource_permission_id,COUNT(resource_permission_id) > 0 AS permitted 
                    FROM acl_role_resource_permission arr 
                    WHERE arr.acl_role_id = :role_id 
                    GROUP BY arr.resource_permission_id
                ) AS permitted ON acl_resource_permission.id = permitted.resource_permission_id 
                WHERE $where 
                GROUP BY acl_resource_permission.id";

        $permissions = DB::select($sql,['resource'=>$request->get('resource'),'role_id'=>$request->get('role_id')]);

        foreach($permissions as $k=>$v) {
            $permissions[$k]->permitted = (int)$v->permitted;
        }

        return $permissions;

    }
                        

        
    public function getRolePermissions(Request $request) {

        $sql = <<<EOF
        select ar.* from acl_role_resource rr join acl_resource ar on rr.resource = ar.acl_name where acl_role_id = :role_id
EOF;
				
        $results = DB::select($sql,['role_id'=>$request->get('role')]);



        return $results;
    }
        

                
                
     
    public function roleList(Request $request){
            $society_id = OauthToken::find(\Input::get('access_token'))->society_id;

            if($request->has('building_id')){
                $society_id  = $request->get('building_id');
            }

            $parent_id = \Input::get('parent_id',NULL);
            if($parent_id){
                $where = 'role.parent_id = '.$parent_id;
            }else{
                $where = 'role.parent_id IS NULL';
            }

            $sql = <<<EOF

        SELECT role.id,role.role_name,COUNT(child.id) AS children,role.is_unique,role.is_default,role.parent_id  FROM acl_role AS role LEFT JOIN acl_role AS parent ON role.id = parent.parent_id LEFT JOIN acl_role AS child ON child.id = parent.parent_id
         WHERE role.society_id = :society_id AND $where GROUP BY role.id
        	
EOF;
        	

        return array_map(function($society)
        {
            foreach ($society as $key => $field) {

                if (is_object($society)) {
                    $society->$key = (int) is_numeric($field)  ? (int) $field :  $field ;
                    continue;
                }

                $society[$key] = (int) is_numeric($field)  ? (int) $field :  $field ;
            }

            return $society;
        }, DB::select($sql,['society_id'=>$society_id]));
    }

    public function saveRole (Request $request) {
        $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
        if($request->has('building_id')){
            $society_id  = $request->get('building_id');
        }
        $validator = \Validator::make(
            $request->all(),
            array(  
                'role_name' => 'required'
                )  
            );

    if ($validator->fails())
        return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];

        $role = AclRole::where('society_id','=',$society_id)
                        ->where('role_name','=',$request->get('role_name'))->first();
        if ($role) {
            return ['success'=>false,'msg'=>'Role \''.$request->get('role_name').'\' already exists.'];
        }
        $role = new AclRole();
        $role->role_name = $request->get('role_name');
        $role->society_id = $society_id;
        $role->parent_id = $request->get('parent_id',NULL);
        $role->save();
        return ['success'=>true,'msg'=>'roles added','roles'=> $role];
    }
        
    public function userModulePermissions(Request $request) {

        $sql = <<<EOF

        select ar.acl_name,ifnull(permitted,0) permitted
        from acl_resource ar 
        inner join acl_resource_permission arp on arp.resource_acl_name = ar.acl_name
        left join (
            select count(aurp.user_id) permitted,aurp.resource_permission_id from oauth_token
            inner join user_society on user_society.society_id = oauth_token.society_id
            inner join users on users.id = user_society.user_id
            inner join acl_user_resource_permission aurp on aurp.user_id = users.id and aurp.society_id = oauth_token.society_id
            where oauth_token.token = :access_token and users.id = :user_id 
            group by aurp.resource_permission_id
        ) as user_permission on user_permission.resource_permission_id = arp.id
        group by ar.acl_name
EOF;
		
        $results = DB::select($sql,
                [
                        'access_token'=>$request->get('access_token'),
                        'user_id'=>$request->get('user_id')
                ]
                );

        return $results;

    }
	
	
    
    /*
     * Get all modules with checked(0,1) with respect to given role_id
     * @access_level  0:Both, 1:Society only, 2:Building only
     */
    public function getModules(Request $request) {

        if($request->has('building_id')){ // To check building level access
            $where = 'acl_resource.access_level != 1';   // Don't show only society level modules like Manage Building
        }else{
           $where = 'acl_resource.access_level != 2';  // Don't show only building level modules
        }

        $sql = "select acl_role_resource.resource,permitted.permitted,acl_resource.title as display from oauth_token 

        inner join acl_role on acl_role.society_id = oauth_token.society_id and acl_role.role_name = 'Admin'
        inner join acl_role_resource on acl_role_resource.acl_role_id = acl_role.id
        inner join acl_resource on acl_resource.acl_name = acl_role_resource.resource 
        left join
        (
             select arr.resource,count(ar.id) > 0 as permitted 
             from acl_role_resource arr 
             inner join acl_role ar on ar.id = arr.acl_role_id
             where ar.id = :role_id group by arr.resource
        ) as permitted on permitted.resource = acl_role_resource.resource
        where oauth_token.token = :token and $where
        group by acl_role_resource.resource";

        $permissions = DB::select($sql,['token'=>$request->get('access_token'),'role_id'=>$request->get('role_id')]);
        foreach($permissions as $k=>$v) {
            $permissions[$k]->permitted = (int)$v->permitted;
        }

        return $permissions;
    }
	
    public function userRoles(Request $request) {
        $society_id = OauthToken::find(\Input::get('access_token'))->society_id;

        $parent_id = $request->get('parent_id',NULL);
        if($parent_id){
            $where = 'acl_role.parent_id = '.$parent_id;
        }else{
            $where = 'acl_role.parent_id IS NULL';
        }

        if($request->has('building_id')){
            $society_id  = $request->get('building_id');
        }

        $sql = <<<EOF

        select acl_role.id,acl_role.role_name,permitted.permitted,COUNT(child.id) AS children from acl_role 


        left join
        (
          select acl_user_role.acl_role_id,count(acl_user_role.user_id) > 0 permitted from acl_user_role 
          where acl_user_role.user_id = :user_id group by acl_user_role.acl_role_id
        ) as permitted on permitted.acl_role_id = acl_role.id
        LEFT JOIN acl_role AS parent ON acl_role.id = parent.parent_id LEFT JOIN acl_role AS child ON child.id = parent.parent_id                
        where acl_role.society_id = :society_id and $where GROUP BY acl_role.id;
		
EOF;
		
        $permissions = DB::select($sql,['society_id'=>$society_id,'user_id'=>$request->get('user_id')]);
        foreach($permissions as $k=>$v) {
            $permissions[$k]->permitted = (int)$v->permitted;
            $permissions[$k]->children = (int)$v->children;
        }

        return $permissions;
    }
	
    public function addUserModuleAccess(Request $request) {


        $permitted = $request->get('permitted');
        $resource = AclResource::find($request->get('acl_name'));
        $token = OauthToken::find($request->get('access_token'));
        if ($permitted == 1) {
            $user = User::find($request->get('user_id'),['id']);
            $society = $token->society()->first();
            $userResource = new AclUserResource();
            $userResource->user()->associate($user);
            $userResource->society()->associate($society);
            $userResource->resource()->associate($resource);
            $userResource->save();

        } else {

            $userResource = AclUserResource::where('acl_resource','=',$request->get('acl_name'))
            ->where('user_id','=',$request->get('user_id'))
            ->where('society_id','=',$token->society_id);
            $userResource->delete();
        }

        return ['success'=>true,'msg'=>'Module access updated successfully'];

    }
	
    public function deleteRole(Request $request) {

        $id = $request->get('role_id');
        $sql = 'select count(*) as total from acl_user_role where acl_role_id = :acl_role_id';
        $result = DB::selectOne($sql,['acl_role_id'=>$id]);

        if($result->total){
            return ['msg'=>'users are assigned to this role','block_error'=>'users are assigned to this role','success'=>false];
        }else{
            $aclrole = AclRole::find($id);
            $aclrole->delete();
            return ['msg'=>'Role deleted successfully','success'=>true];
        }

    }

}

