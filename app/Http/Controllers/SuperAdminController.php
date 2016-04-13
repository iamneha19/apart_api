<?php
namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\AclResource;
use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\AclRoleResource;
use Illuminate\Http\Request;
use DB;

class SuperAdminController extends Controller {
	
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('super_admin_rest');
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
	
	
	public function addUserModuleAccess(Request $request) {
		
		$permitted = $request->get('permitted');
		$resource = AclResource::find($request->get('acl_name'));
		
		if ($permitted == 1) {
			$role = AclRole::where('role_name','=','Admin')
					->where('society_id','=',$request->get('society_id'))->first();
						
			$roleResource = new AclRoleResource();
			$roleResource->role()->associate($role);
			$roleResource->aclresource()->associate($resource);
			$roleResource->save();
				
		} else {
			
			DB::delete('DELETE acl_role_resource from acl_role_resource  JOIN acl_role on acl_role.id = acl_role_resource.acl_role_id
				WHERE acl_role.society_id = :society_id AND acl_role_resource.resource = :resource',
					['society_id'=>$request->get('society_id'),'resource'=>$request->get('acl_name')]);
            
            DB::delete('DELETE acl_role_resource from acl_role_resource  JOIN acl_role on acl_role.id = acl_role_resource.acl_role_id
                        WHERE society_id IN (SELECT society.id FROM society WHERE parent_id = :society_id) AND acl_role_resource.resource = :resource',
                        ['society_id'=>$request->get('society_id'),'resource'=>$request->get('acl_name')]);
			
		}
		
		return ['success'=>true,'msg'=>'Module access updated successfully'];
		
	}
	
	public function userModuleAccessList() {
		
		$sql = <<<EOF
		select * from oauth_token inner join user_society us on us.society_id = oauth_token.society_id
		acl_resource 
		left join acl_user_resource on acl_resource.acl_name = acl_user_resource.resource_acl_name
		where 
		
EOF;
		
	}
	
	public function listSociety(Request $request) {
                
                $values = ['role'=>'admin'];
                
               $bindings = array(
                            'limit'=>\Input::get('limit',20),
                            'offset'=>\Input::get('offset',0)
                        );
               $where = '';
               if (\Input::get('search',null)) {
                    $where = ' and society.name like :search';
                    $values['search'] = '%'.\Input::get('search').'%';
                }
                
		$societies = DB::select("
                                    select society.id as society_id,society.created_at, acl_user_role.user_id, 
                                    society.name,category.name as type,society.pincode,users.first_name,users.last_name
                                     from acl_user_role 
                                     inner join acl_role on acl_role.id = acl_user_role.acl_role_id
                                     inner join society on acl_role.society_id = society.id
                                     left join category on category.id = society.society_category_id
                                     inner join users on acl_user_role.user_id = users.id
                                     where acl_role.role_name = :role $where order by society.id desc limit :limit offset :offset",
                            array_merge($values,$bindings)
                        );
                
                $count = DB::selectOne(
                    "select count(society.id) total
                    from acl_user_role 
					 inner join acl_role on acl_role.id = acl_user_role.acl_role_id
					 inner join society on acl_role.society_id = society.id 
					 inner join users on acl_user_role.user_id = users.id
					 where acl_role.role_name = :role $where",$values
                );
	
		return ['total'=>$count->total,'success'=>true,'data'=>$societies,'msg'=>'Successfully fetched societies,'];
	
	}
        
        public function getModulePermissions(Request $request) {
        	
            $attr = ['societyId'=>$request->get('societyId'),'userId'=>$request->get('userId')];
            
            $sql = <<<EOF
	    select acl_resource.acl_name,acl_resource.title,permitted.permitted from acl_resource

		left join
		(
		   select arr.resource,count(arr.acl_role_id) > 0 permitted from acl_role_resource arr 
            inner join acl_role on acl_role.id = arr.acl_role_id
		   inner join acl_user_role aur on aur.acl_role_id = arr.acl_role_id
		   where aur.user_id = :userId and acl_role.society_id = :societyId group by arr.resource
		) as permitted on permitted.resource = acl_resource.acl_name
        where acl_resource.access_level != 2            
		group by acl_resource.acl_name        
            
EOF;
//             $permissions = DB::select('select acl_resource.acl_name,cast(count(aur.user_id) > 0 as unsigned) as permitted from acl_resource
//                                         left join acl_user_resource aur on aur.acl_resource = acl_resource.acl_name and aur.user_id = :userId
//                                         and aur.society_id = :societyId group by acl_resource.acl_name',$attr);
            
            $permissions = DB::select($sql,$attr);
            
            foreach($permissions as $k=>$v) {
            	$permissions[$k]->permitted = (int)$v->permitted;
            }
            return  $permissions;
        }
	
}
