<?php
namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Commands\Acl\Extractor\PermissionExtractor;
use ApartmentApi\Events\ResetPassword;
use ApartmentApi\Events\SocietyJoinRequest;
use ApartmentApi\Events\SocietyWasRegistered;
use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\AclRoleResource;
use ApartmentApi\Models\AclRoleResourcePermission;
use ApartmentApi\Models\AclUserRole;
use ApartmentApi\Models\Block;
use ApartmentApi\Models\Building;
use ApartmentApi\Models\Complex;
use ApartmentApi\Models\Flat;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\Society;
use ApartmentApi\Models\SocietyRole;
use ApartmentApi\Models\SocietyRoleResource;
use ApartmentApi\Models\SocietyRoleResourcePermission;
use ApartmentApi\Models\User;
use ApartmentApi\Models\UserSociety;
use Illuminate\Http\Request;
use Validator;
use ApartmentApi\Models\SocietyConfig;

class SocietyController extends Controller
{

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('rest',['except'=>['checkOccupancy','listAction','create','join','checkEmail','verifyEmail','search','buildings','blocks','checkEmailexists']]);
	}


	public function create(Request $request)
    {
       $attributes = $request->all();

      
       $validator = Validator::make($attributes, [
                        'name'       => 'required',
                        'society_category_type' => 'required',
                        'addressLine2'=>'required',
                        'first_name' => 'required',
                        'last_name'  => 'required',
                        'email'      => 'required|email',
                        'state_id'   => 'required',
                        'city_id'    => 'required',
                        'street'     => 'required',
                        'pincode' => 'required',
                    ]);

        if ($validator->fails())
        {
            $response = ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];
        }
        
        $data = User::where('email','=',$attributes['email'])->count();
        if($data)
        {
            return ['msg'=>'Email id is already exist','email'=>'already exists','success'=>false];
        }

        // To check email is already registered
//        exit;
        $user = User::where('email','=',$attributes['email'])->first(['id','first_name', 'last_name', 'email']);

//                $role = AclRole::find($attributes['role']);
//                if (!$role)
//                $response =  ['msg'=>'Role does not exist','success'=>false];


        if (!$user)
        {
            $user = new User();
            $user->fill($attributes);
            $password = str_random(8);
            $user->password = bcrypt($password);
            $user->save();
        }

		$society = new Society();
		$society->fill($attributes);
		$society->save();


		$complex = new Complex();
		$complex->id = $society->id;
		$complex->fill($attributes);
		$complex->save();

		$userSociety = new UserSociety();
		$userSociety->user()->associate($user);
		$userSociety->society()->associate($society);
		$userSociety->relation = \Input::get('relation');
        $userSociety->status = 1;
		$userSociety->save();


        $societyRoles = SocietyRole::all();
        foreach($societyRoles as $societyRole){

            $aclRole = new AclRole();
            $aclRole->role_name = $societyRole->role_title;
            $aclRole->is_unique = $societyRole->is_unique;
            $aclRole->is_default = 1;
            $aclRole->society()->associate($society);
            $aclRole->save();

            if($aclRole->role_name == 'Admin'){
                $aclUserRole = new AclUserRole();
                $aclUserRole->user()->associate($user);
                $aclUserRole->aclRole()->associate($aclRole);
                $aclUserRole->save();
            }

            $resourcesAll = SocietyRoleResource::where('society_role_id','=',$societyRole->id)->get();

            foreach($resourcesAll as $resource){
                $aclRoleResource = new AclRoleResource();
                $aclRoleResource->resource = $resource->resource;
                $aclRoleResource->acl_role_id = $aclRole->id;
                $aclRoleResource->save();
            }

            $permissionsAll = SocietyRoleResourcePermission::where('society_role_id','=',$societyRole->id)->get();

            foreach($permissionsAll as $permission){
                $aclRoleResource = new AclRoleResourcePermission();
                $aclRoleResource->resource_permission_id = $permission->resource_permission_id;
                $aclRoleResource->acl_role_id = $aclRole->id;
                $aclRoleResource->save();
            }
        }

        $data = array(
                        'name'=>$user->first_name.' '.$user->last_name,
                        'email'=>$user->email,
                        'password'=>(!empty($password)) ? $password : 'Your account is already exist.Please use your previous password for login.',
                        'society'=>$society->name
                    );
        event(new SocietyWasRegistered($data));

        $response =  ['success'=>true,'msg'=>'society created successfully'];        
        $societyConfig = new SocietyConfig();
        $societyConfig->society_id = $society->id;
        $societyConfig->is_approved ="NO";
        $societyConfig->save();
        
        return response()->json([
                            'status_code'=>$request->server->get('REDIRECT_STATUS'),
                            'response'=>$response
        ]);


	}

    public function join(Request $request) {


		$attributes = $request->all();

        $block_id = $request->get('block_id',NULL);
        $flat_no = $attributes['flat_no'];
        $building_id = $attributes['building_id'];
		//dd(isset($attributes['block_id']));
        $society_id = $attributes['society_id'];

        $society = Society::where('id','=',$society_id)->first(['id']);
        if (!$society){
             $response =  ['msg'=>'society with id - '.$society_id.' does not exist','success'=>false];
            return response()->json([
                                'status_code'=>$request->server->get('REDIRECT_STATUS'),
                                'response'=>$response
            ]);
        }

        $validator = \Validator::make(
                            $attributes,
                            array(
                                    'first_name' => 'required',
                                    'last_name' => 'required',
                                    'email'=>'required|email',
                                    'flat_no'=>'required',
                                    'relation'=>'required'
                                )
        );

        if ($validator->fails()){
            $response = ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];
        }

        $sql = 'select user_society.relation,flat.id from user_society inner join flat on user_society.flat_id = flat.id
        		where user_society.building_id = :building_id and user_society.block_id = :block_id and flat.flat_no = :flat_no
        	';
        //dd($sql);
        $duplicate = \DB::select($sql,array('building_id'=>$building_id,'block_id'=>$block_id,'flat_no'=>$flat_no));

        if($duplicate){
            $response =  ['flat_error'=>'This flat is already registered with this society','success'=>false];
            return response()->json([
                                'status_code'=>$request->server->get('REDIRECT_STATUS'),
                                'response'=>$response
            ]);

        }

        // To check email is already registered
        $user = User::where('email','=',$attributes['email'])->first(['id','first_name', 'last_name', 'email']);

        if(!$user){
            $user = new User();
            $user->fill($attributes);
            $password = str_random(8);
            $user->password = bcrypt($password);
            $user->save();
        }






//        $block = Block::where('society_id','=',$society_id)
//				->where('block','=',$attributes['block'])
//				->first(['id']);
//
//
//        if(!$block){ // If block already present
//           $block = new Block();
//            $block->block = \Input::get('block');
//            $block->society()->associate($society);
//            $block->save();
//        }
//
//		$flat = Flat::where('flat_no','=',$attributes['flat_no'])
//				->where('block_id','=',$block->id)
//				->first(['id']);
//
//		if(!$flat){ // If flat already present
//            $flat = new Flat();
//            $flat->fill($attributes);
//            $flat->block()->associate($block);
//            $flat->save();
//        }

        $building = Building::find($request->get('building_id'));
        if($request->has('block_id')){
                $block = Block::find($request->get('block_id'));

                $flat = new Flat();
                $flat->fill($request->all());
                $flat->block()->associate($block);
                $flat->save();

                $userSociety = new UserSociety();
                $userSociety->user()->associate($user);
                $userSociety->society()->associate($society);
                $userSociety->building()->associate($building);
                $userSociety->block()->associate($block);
                $userSociety->flat()->associate($flat);
                $userSociety->relation = $request->get('relation');
                $userSociety->status = 2;
                $userSociety->save();

            }else{
                $flat = new Flat();
                $flat->fill($request->all());
                $flat->save();

                $userSociety = new UserSociety();
                $userSociety->user()->associate($user);
                $userSociety->society()->associate($society);
                $userSociety->building()->associate($building);

                $userSociety->flat()->associate($flat);
                $userSociety->relation = $request->get('relation');
                $userSociety->status = 2;
                $userSociety->save();
        }

//		$userSociety = new UserSociety();
//		$userSociety->user()->associate($user);
//		$userSociety->society()->associate($society);
//		$userSociety->block()->associate($block);
//		$userSociety->flat()->associate($flat);
//		$userSociety->relation = \Input::get('relation');
//        $userSociety->status = 2;
//		$userSociety->save();



        $data = array(
                        'name'=>$user->first_name.' '.$user->last_name,
                        'email'=>$user->email,
                        'password'=>(!empty($password)) ? $password : 'Your account is already exist.Please use your previous password for login.',
                        'society'=>$society->name
                    );
        event(new SocietyJoinRequest($data));

        $response =  ['success'=>true,'msg'=>'Successfully sent join request'];

        return response()->json([
                            'status_code'=>$request->server->get('REDIRECT_STATUS'),
                            'response'=>$response
        ]);


	}

	public function listAction() {

		return \DB::select('select * from society');

	}

	public function switchSociety(Request $request) {

		$societyId 	= $request->get('society_id');

		$sql = <<<EOF

		select user_society.society_id from oauth_token
		inner join user_society on user_society.user_id = oauth_token.user_id
		and user_society.society_id = :society_id
		where oauth_token.token = :token

EOF;

		$switchToId = \DB::selectOne($sql,
				['token'=>$request->get('access_token'),'society_id'=>$societyId]);

		$token = OauthToken::find($request->get('access_token'));

		$token->society_id = $switchToId->society_id;

		$token->save();

//		$modules = $this->getModules($token->token);

        $permissionExtrator = new PermissionExtractor($token->society_id,$token->user_id);

        $moduleAcl =  $this->dispatch($permissionExtrator);
        $moduleAcl['building_id'] = $token->getAclBuildingId($token->society_id,$token->user_id);
		return ['success'=>true,
				'user'=>app('services')->getUser($token->token),
                'acl'=>$moduleAcl

		];

	}

	public function getModules($accessToken) {

		$sql = <<<EOF
select ar.acl_name as module,ar.type,ar.title,ar.route,ar.icon from oauth_token
inner join acl_role aro on aro.society_id = oauth_token.society_id
inner join acl_role_resource arr on arr.acl_role_id = aro.id
inner join acl_user_role aur on aur.acl_role_id = arr.acl_role_id and aur.user_id = oauth_token.user_id
inner join acl_resource ar on ar.acl_name = arr.resource
where oauth_token.token = :token group by ar.acl_name;

EOF;
		return \DB::select($sql,['token'=>$accessToken]);

	}


        public function checkEmail(Request $request){

            $email 	= $request->get('email');
            $sql = <<<EOF

		select society.name from users
		inner join user_society on users.id = user_society.user_id
                inner join society on user_society.society_id = society.id
		where users.email = :email and user_society.status = 1

EOF;
            $society = \DB::selectOne($sql,['email'=>$email]);
            if($society){
                 $response = ['success'=>false,'msg'=>'This email is already registered with '.$society->name.'. Do you really want to continue?'];
            }else{
                $response = ['success'=>true,'msg'=>'This email is not registered'];
            }

            return response()->json([
				'status_code'=>$request->server->get('REDIRECT_STATUS'),
				'response'=>$response
            ]);

        }

        public function checkFlat(Request $request){

            $flat_no 	= $request->get('flat_no');
            $block_id 	= $request->get('block_id');
            $sql = <<<EOF

		select count(*) as total from flat
		where block_id = :block_id and flat_no = :flat_no

EOF;
            $flat = \DB::selectOne($sql,['block_id'=>$block_id,'flat_no'=>$flat_no]);
            if($flat->total){
                return ['success'=>false,'msg'=>'This flat is already taken'];
            }else{
               return ['success'=>true,'msg'=>'This flat is not taken'];
            }

        }

        /**Check email address is valid or not for forgot password link **/
        public function verifyEmail()
        {
            $email_id = $_POST['email'];
//            echo \Request::url();exit;
            $check_email = User::where('email','=',$email_id)->first();
            if($check_email)
            {
                $password = str_random(8);
                $check_email->password_changed = '0';
                $check_email->password = bcrypt($password);
                $check_email->save();
                $data = array(
                    'username'=>$check_email->first_name.' '.$check_email->last_name,
                    'password'=>$password,
                    'email'=>$email_id,
                );
                event(new ResetPassword($data));
                return ['success'=>true,'msg'=>'Email Address is correct'];
            }else{
                return ['success'=>false,'msg'=>'Invalid Username!'];
            }
        }

        public function search(Request $request)
        {
            $search 	= $request->get('search');
            $sql = <<<EOF

		select `society`.`name`, `society`.`id`,society.address from society
		where society.name like :search and society.parent_id is NULL and society.deleted_at is NULL

EOF;
            $societies = \DB::select($sql,['search'=>'%'.$search.'%']);
            if($societies){
                return ['success'=>true,'data'=>$societies,'msg'=>'Successfully fetched societies.'];
            }else{
               return ['success'=>false,'msg'=>'This flat is not taken'];
            }
        }

        public function saveBuilding(Request $request) {

        	OauthToken::find($request->get('access_token'))->society_id;

        	$building = new Building();

        	$building->fill($request->all());

        	$building->save();

        }


        public function getSocietyInfo()
        {
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
//            print_r($society_id);exit;
            $sql = "SELECT society.google_map_src,society.name,category.name as type,category.id as typeId,society.address,complex.state_id,complex.address_line_2,complex.city_id,complex.pincode,complex.nearest_station,complex.landmark,state.name AS state,city.name AS city FROM society "
                    . "INNER JOIN complex ON complex.id=society.id "
                    . "INNER JOIN state ON complex.state_id=state.id "
                    . "left JOIN category on society.society_category_id = category.id "
                    . "INNER JOIN city ON complex.city_id=city.id WHERE society.id= :society_id";
            $data = \DB::selectOne($sql,['society_id'=>$society_id]);
//            print_r($data);exit;
            if($data){
                return array(
                    'data'=>$data,
                );
            }else{
                return ['msg'=>'No records found'];
            }

        }

        public function updateSocietyInfo(Request $request)
        {
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            $attributes = \Input::all();
            $complex = Complex::where('id','=',$society_id)
				->firstOrFail();
            $complex->fill($attributes);

            $complex->save();
//            print_r($attributes);
            $society = Society::where('id','=',$society_id)
				->firstOrFail();
            $society->google_map_src = $request->get('google_map_src');
            $society->fill($attributes);
            $society->save();

            return ['success'=>true,'msg'=>'Society updated successfully!'];
        }

        public function buildings($societyId, Request $request) {

            $buildings = \DB::select('
				select society.id,society.name,building.flats,building.floors,building.blocks
				from society inner join building on building.id = society.id
				where society.parent_id = :society_id
				',

				['society_id'=>$societyId]);

           $response =  ['success'=>true,'data'=>$buildings,'msg'=>'Successfully fetched buliding.'];

            return response()->json([
				'status_code'=>$request->server->get('REDIRECT_STATUS'),
				'response'=>$response
            ]);
        }

        public function blocks($buildingId, Request $request) {

		$blocks =  Block::where('society_id','=',$buildingId)
								->get(['id','block']);

        $response =  ['success'=>true,'data'=>$blocks,'msg'=>'Successfully fetched blocks.'];

        return response()->json([
            'status_code'=>$request->server->get('REDIRECT_STATUS'),
            'response'=>$response
        ]);
	}

	public function checkOccupancy(Request $request) {

// 		dd([
// 				'block_id'		=>	$request->has('block_id') ? $request->get('block_id') : null,
// 				'flat_no'		=>	$request->get('flat_no'),
// 				'relation'		=>	$request->get('relation'),
// 				'building_id'	=>	$request->get('building_id')
// 		]);
		$sql = 'select count(user_society.id) as total from user_society join flat on user_society.flat_id = flat.id
        		where user_society.building_id = :building_id and user_society.block_id = :block_id
				and flat.flat_no = :flat_no and user_society.relation = :relation and user_society.status = 1';

		$result = \DB::selectOne($sql,[
				'block_id'		=>	$request->has('block_id') ? $request->get('block_id') : null,
				'flat_no'		=>	$request->get('flat_no'),
				'relation'		=>	$request->get('relation'),
				'building_id'	=>	$request->get('building_id')
		]);

		if($result->total)
			return "false";
		else
			return "true";


	}

    public function checkEmailexists(Request $request)
    {
        $email_id 	= $request->get('email_id');
        $data = User::where('email','=',$email_id)->first();

        if($data)
        {
            return ['msg'=>'Email id is already exist','success'=>false];
        }else{
            return ['msg'=>'Success','success'=>true];
        }
    }

}
