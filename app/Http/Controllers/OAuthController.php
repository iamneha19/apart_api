<?php namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Commands\Acl\Extractor\PermissionExtractor;
use ApartmentApi\Commands\Acl\Extractor\SocietiesExtractor;
use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Models\OauthClient;
use ApartmentApi\Models\OauthSuperAdminToken;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\User;
use ApartmentApi\Models\UserSociety;
use Illuminate\Http\Request;
use DB;
use Illuminate\Bus\Dispatcher;
use ApartmentApi\Commands\User\IsFlatAttached;

class OAuthController extends Controller
{

	public function issueAccessToken(Request $request, Dispatcher $dispatcher) {

		$email = \Input::get('email',null);
		$password = \Input::get('password',null);
		$client = OauthClient::findOrFail(\Input::get('client_id'));                
		if (\Auth::validate(['email' => $email, 'password' => $password]))
		{
			$user = User::where('email','=',$email)->firstOrFail();

			$token = OauthToken::where([
					'user_id'=>$user->id,
					'client_id' =>$client->id
			])->first();


			if(!$token) {

				$token = new OauthToken();
				$token->token = md5(
						base64_encode(
								pack('N6', mt_rand(), mt_rand(), mt_rand(), mt_rand(), microtime(true), uniqid(mt_rand(), true))
						)
				);

				$token->user()->associate($user);
				$token->client()->associate($client);
				$token->save();

			}


                        $societiesExtractor = new SocietiesExtractor($token->user_id);
			$societies = $this->dispatch($societiesExtractor);                        
			if(!$societies)
				return ['success'=>false,'msg'=>'You dont have access to any societies.Please contact society\'s administrator'];

			if (count($societies) > 0) {
				$token->society_id = $societies[0]->id;
				$token->save();


                $permissionExtrator = new PermissionExtractor($token->society_id,$token->user_id);

                $moduleAcl =  $this->dispatch($permissionExtrator);
                $moduleAcl['building_id'] = $token->getAclBuildingId($token->society_id,$token->user_id);

			  $role_name='';

			if(!empty($moduleAcl['role_name'])){
			  $role_name=$moduleAcl['role_name'];                          
			  unset($moduleAcl['role_name']);
			}
            $user = \App::make('services')->getUser($token->token);
            

            if (! $dispatcher->dispatch(new IsFlatAttached($user->user_id))) {
                return response()->json([
                    'status_code' => 400,
                    'status' => false,
                    'msg' => 'No flats are attached to you, please contact to your society administrator.'
                ]);
            }
				return response()->json([
						'status_code'=>$request->server->get('REDIRECT_STATUS'),
						'success'=>true,
						'access_token'=>$token->token,
						'user'=>$user,
						'role_name'=>  $role_name,
						'socities'=>$societies,
//						'acl'=>[
//
//							'admin' => array_filter($modules, function($el){
//								return $el->type == 1;
//							}),
//							'resident' => array_filter($modules, function ($el){
//								return $el->type == 0;
//							})
//						],
                        'acl'=> $moduleAcl,


				]);

			} else {

				return response()->json([
						'status_code'=>$request->server->get('REDIRECT_STATUS'),
						'success'=>false,
						'msg'=>'Your account is not activated.'

				]);

			}

		} else {

			return response()->json([
					'status_code'=>$request->server->get('REDIRECT_STATUS'),
					'success'=>false,
					'msg'=>'Invalid credentials'

			]);
		}


	}




	public function getAclForAccessToken($accessToken) {

		$sql = <<<EOF

		select ar.acl_name as module,group_concat(concat(arp.resource_acl_name,'.',arp.permission)) as routes from oauth_token
		inner join acl_user_resource_permission aurp on aurp.user_id = oauth_token.user_id
		and aurp.society_id = oauth_token.society_id
		inner join acl_resource_permission as arp on arp.id = aurp.resource_permission_id
		inner join acl_resource as ar on ar.acl_name = arp.resource_acl_name
				where oauth_token.token = :token
		group by ar.acl_name

EOF;

		$acls = \DB::select($sql,['token'=>$accessToken]);

		foreach ($acls as $key=>$acl) {
			$acls[$key]->routes = explode(',', $acls[$key]->routes);
		}

		return $acls;
	}


    public function superAdminlogin(Request $request) {
                $email = \Input::get('email',null);
                $password = \Input::get('password',null);
                $client = OauthClient::findOrFail(\Input::get('client_id'));

                if (\Auth::validate(['email' => $email, 'password' => $password]))
                {
                    $user = User::where('email','=',$email)->where('admin_user','=',1)->first();
                    if(!$user){ // If it's not admin user
                        return response()->json([
                                                'status_code'=>$request->server->get('REDIRECT_STATUS'),
                                                'success'=>false,
                                                'msg'=>'Invalid credentials'

                                ]);
                    }
                    $token = OauthSuperAdminToken::where([
                                    'user_id'=>$user->id,
                                    'client_id' =>$client->id
                    ])->first();


                    if(!$token) {

                            $token = new OauthSuperAdminToken();
                            $token->token = md5(
                                            base64_encode(
                                                            pack('N6', mt_rand(), mt_rand(), mt_rand(), mt_rand(), microtime(true), uniqid(mt_rand(), true))
                                            )
                            );

                            $token->user()->associate($user);
                            $token->client()->associate($client);
                            $token->save();

                            return response()->json([
						'status_code'=>$request->server->get('REDIRECT_STATUS'),
						'success'=>true,
                                                'user'=>\App::make('services')->getSuperAdminUser($token->token),
						'access_token'=>$token->token
                            ]);

                    }else{
                        return response()->json([
						'status_code'=>$request->server->get('REDIRECT_STATUS'),
						'success'=>true,
                                                'user'=>\App::make('services')->getSuperAdminUser($token->token),
						'access_token'=>$token->token
                            ]);
                    }
                }else {

                            return response()->json([
                                            'status_code'=>$request->server->get('REDIRECT_STATUS'),
                                            'success'=>false,
                                            'msg'=>'Invalid credentials'

                            ]);
                }
        }

        /*
         * Get permission type i.e 'society','building'
         */
        public function getPermissionType(Request $request)
        {
            $token = $request->get('access_token');
            $permission = $request->get('permission');
            $oauthToken = OauthToken::find($token);
            $societyId = $oauthToken->society_id;
            $userId = $oauthToken->user_id;

            $societyWisePermission = false;
            $buildingWisePermission = false;
            $aclBuildingId = NULL;


            $hasSocietyPermission = $oauthToken->hasSocietyPermission($permission, $token);
            $hasBuildingPermission = $oauthToken->hasBuildingPermission($permission, $societyId, $userId);

            if ($hasBuildingPermission) {
                $buildingWisePermission = true;
                $aclBuildingId = $oauthToken->getAclBuildingId($societyId, $userId);
            }

            if ($hasSocietyPermission) {
                $societyWisePermission = true;

            }


            if(!$societyWisePermission && !$buildingWisePermission){
                return response()->json([
                                        'status_code'=>401,
                                        'msg'=>'You don\'t have any permission',
                                        'success'=>false,
                        ]);
            }else{

                return response()->json([   'status_code'=>200,
                                            'data'=>['society_permission'=>$societyWisePermission,'building_permission'=>$buildingWisePermission,'building_id'=>$aclBuildingId],
                                            'success'=>true,
                            ]);
            }

        }
}
