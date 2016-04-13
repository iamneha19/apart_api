<?php namespace ApartmentApi\Http\Middleware;

use ApartmentApi\Models\OauthToken;
use Closure;
use DB;
use Session;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Http\Request;

class AclMiddleware implements SelfHandling {

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		$token = OauthToken::find($request->get('access_token'),['token','society_id','user_id']);
        		
		if (!$token){
           return response()->json([
				'status_code'=>403,
				'msg'=>'Invalid Access Token'
			]); 
        }
			
		
 		if (!$request->route()->getName()) {
 			return response()->json([
				'status_code'=>500,
				'msg'=>'Route name is not defined'
			]);
 		}
		
        $user = $token->user()->first();
        $society = $token->society()->first();
        
        $societyPermission = $this->getSocietyPermission($request->route()->getName(),$token->society_id,$token->user_id);
        $buildingPermission = $this->getBuildingPermission($request->route()->getName(),$token->society_id,$token->user_id);
        if($societyPermission->total || $buildingPermission->total){
            $permission = array();
            if($societyPermission->total){
                $permission['society_id']= $societyPermission->society_id;
            }
            if($buildingPermission->total){
               $permission['building_id']= $buildingPermission->building_id; 
            }
            
            Session::put('acl.user', $user);
            Session::put('acl.society', $society);
            Session::put('acl.permission', $permission); 
            
            return $next($request);
            
        }else{
            return response()->json([
				'status_code'=>401,
				'msg'=>'Unauthorized access'
			]);
        }
	
	}
    
    private function getSocietyPermission($permission,$societyId,$userId)
    {
        $sql = <<<EOF
		
		SELECT COUNT(*) AS total,society_id FROM acl_role aro
        INNER JOIN acl_user_role aur ON  aro.id = aur.acl_role_id
        INNER JOIN acl_role_resource_permission arrp ON aro.id = arrp.acl_role_id
        INNER JOIN acl_resource_permission arp ON arrp.resource_permission_id = arp.id 
        WHERE aro.society_id = :society_id AND aur.user_id = :user_id AND arp.permission = :permission;

EOF;
		
		$result =  DB::selectOne($sql, ['permission'=>$permission,'society_id'=>$societyId,'user_id'=>$userId]);
        return $result;

    }
    
    private function getBuildingPermission($permission,$societyId,$userId)
    {
        $sql = <<<EOF
		
        SELECT COUNT(*) AS total,society_id AS building_id FROM acl_role aro
        INNER JOIN acl_user_role aur ON  aro.id = aur.acl_role_id
        INNER JOIN acl_role_resource_permission arrp ON aro.id = arrp.acl_role_id
        INNER JOIN acl_resource_permission arp ON arrp.resource_permission_id = arp.id 
        WHERE aro.society_id IN (SELECT society.id FROM society WHERE parent_id = :society_id) AND aur.user_id = :user_id AND arp.permission = :permission;

EOF;
		
        $result =  DB::selectOne($sql, ['permission'=>$permission,'society_id'=>$societyId,'user_id'=>$userId]);
        return $result;
        
    }
    
}
