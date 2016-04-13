<?php

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\Society;
use ApartmentApi\Models\User;
use ApartmentApi\Models\Flat;
use ApartmentApi\Models\Block;
use ApartmentApi\Models\UserSociety;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\AclUserRole;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Request;
use Input;
use ApartmentApi\Models\Building;
use Illuminate\Validation\Validator;
use ApartmentApi\Models\FlatParking;
use ApartmentApi\Models\Member;

Class UserController extends Controller
{
    protected $input;
    protected $passwords;

    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
    public function __construct(Input $input){
        $this->input = $input;
//        $this->middleware('rest');
        $this->middleware('rest',['except'=>['forgotPwd']]);
    }

    public function getUsersList(Request $request)
    {
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $societyId = $oauthToken->society_id;
        $userId = $oauthToken->user_id;
        $search = $this->input->get('search',null);
        $status = $this->input->get('status');

        $hasSocietyPermission = $oauthToken->hasSocietyPermission('user.list', $request->get('access_token'));
        if ($hasSocietyPermission) {
            $where = 'user_society.society_id="'.$societyId.'" and user_society.status="'.$status.'" and user_society.relation in ("tenant","owner")' ;
        } else {
            $buildingId = $oauthToken->getAclBuildingId($societyId, $userId);
            $where = 'user_society.building_id='.$buildingId.' and user_society.status="'.$status.'" and user_society.relation in ("tenant","owner")' ;
        }
//        $where = 'user_society.society_id="'.$society_id.'" and user_society.status="'.$status.'"' ;

        $sort = '';
        $whereSep = '';
        $bindings = array();
        if ($this->input->get('search',null)) {
            $where .= ' and (users.first_name like :first_name or users.email like :email or users.last_name like :last_name or flat.flat_no like :flat_no)';
            $whereSep = true;
            $bindings['first_name'] = '%'.\Input::get('search').'%';
            $bindings['last_name'] = '%'.\Input::get('search').'%';
            $bindings['flat_no'] = '%'.\Input::get('search').'%';
            $bindings['email'] = '%'.\Input::get('search').'%';
        }

        if (\Input::get('block_id',null)) {
            $where .= ' and user_society.block_id = :block_id';
            $bindings['block_id'] = (int)\Input::get('block_id');
        }
        $where = $where ? ' where '.$where : '';
        if (\Input::get('sort',null)){
           $sort =  \Input::get('sort',null);
            if($sort == 'flat'){
                $sort = ' order by block.block '.\Input::get('sort_order','desc').', flat.flat_no '.\Input::get('sort_order','desc').' ';
            }else{
               $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','desc').' ';
            }

        }
        enableQueryLog();
        $data = \DB::select("select building.name as building,users.id,users.first_name,users.last_name,users.email,users.contact_no,users.created_at, flat.id as flat_id, flat.flat_no, flat.type,
        block.id as block_id, block.block, user_society.society_id, user_society.id as user_society_id,user_society.relation
        from user_society
        inner join users on user_society.user_id = users.id
        inner join society as building on building.id = user_society.building_id
        inner join flat on flat.id = user_society.flat_id
        left join block on block.id = user_society.block_id $where $sort limit :limit offset :offset",
        array_merge($bindings,array(
                            'limit'=>\Input::get('limit',20),
                            'offset'=>\Input::get('offset',0)
                        )
                    )
                );

        $count = \DB::selectOne(
                    "select count(users.id) total
                    from user_society
                    inner join users on user_society.user_id = users.id
                    inner join society as building on building.id = user_society.building_id
                    inner join flat on flat.id = user_society.flat_id
                    left join block on block.id = user_society.block_id
                     $where ",
                        $bindings
                );

            return array(
                'total'=>$count->total,
                'data'=>$data
            );
    }

//    public function getUser($id,$society_id) {
//        $post = \DB::selectOne("select users.id,users.first_name,users.last_name,users.email,users.owner,users.responsibility,
//        users.admin_user,users.designation,users.contact_no,users.created_at,flat.id as flat_id,flat.flat_no,
//        block.id as block_id,block.block,user_society.society_id,user_society.id as user_society_id,user_society.status,acl_user_role.role_acl_name as role
//        from user_society
//        inner join acl_user_role on acl_user_role.user_id = user_society.user_id and acl_user_role.society_id = user_society.society_id
//        inner join users on user_society.user_id = users.id
//        inner join flat on flat.id = user_society.flat_id
//        inner join block on block.id = user_society.block_id where users.id = :id limit 1",['id'=>$id]);
//        if (!$post)
//            return ['msg'=>'user doesnot exist with id - '.$id];
//
//        return $post;
//    }

//    public function getUser($id){
//
//        $oauthToken = OauthToken::find(\Input::get('access_token'));
//        $society_id = $oauthToken->society_id;
////        $user = \DB::selectOne("select users.id,users.first_name,users.last_name,users.email,users.contact_no from users where users.id = :id",['id'=>$id]);
//        $user = \DB::selectOne("SELECT GROUP_CONCAT(acl_user_role.role_acl_name) AS roles,users.id,users.first_name,users.last_name,users.email,users.contact_no,users.dob,users.voter_id,users.unique_id FROM users LEFT JOIN `acl_user_role` ON `users`.id = `acl_user_role`.user_id AND acl_user_role.society_id = :society_id  WHERE users.id = :id",['id'=>$id,'society_id'=>$society_id]);
//        if (!$user)
//            return ['msg'=>'user doesnot exist with id - '.$id, 'success'=>false];
//
//        return ['data'=>$user,'success'=>true];
//    }

    public function getUser($id){
//        print_r($id);exit;

        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $society_id = $oauthToken->society_id;
//        $user = \DB::selectOne("select users.id,users.first_name,users.last_name,users.email,users.contact_no from users where users.id = :id",['id'=>$id]);
        $data = \DB::selectOne("SELECT users.id,users.first_name,users.last_name,users.email,users.contact_no,users.dob,users.voter_id,users.unique_id FROM users WHERE users.id = :id",['id'=>$id]);
//               print_r($data);exit;
        if (!$data)
            return ['msg'=>'user doesnot exist with id '.$society_id];

//         $count = \DB::selectOne(
//                    "select count(users.id) total
//                    from user_society
//                    inner join users on user_society.user_id = users.id
//                     where  user_society.status = 1 and user_society.society_id = :society_id group by user_society.user_id,user_society.society_id",['society_id'=>$society_id]);
            return array(
//                'total'=>$count->total,
                'data'=>$data
            );

    }

    public function findUser(){
        $email = \Request::get('email');

        $oauthToken = OauthToken::find(\Input::get('access_token'));
//        $society_id = $oauthToken->society_id;
//        $user = \DB::selectOne("select users.id,users.first_name,users.last_name,users.email,users.contact_no from users where users.id = :id",['id'=>$id]);
        $user = \DB::selectOne("SELECT users.id,users.first_name,users.last_name,users.email,users.contact_no FROM users WHERE users.email = :email",['email'=>$email]);
        if (!$user)
            return ['msg'=>'user doesnot exist with email - '.$email, 'success'=>false];

        return ['data'=>$user,'success'=>true];
    }

    public function getSocietyUser($id)
    {
        $post = \DB::selectOne("select users.id,users.first_name,users.last_name,users.email,users.contact_no,users.created_at,
                flat.id as flat_id,flat.flat_no, block.id as block_id,block.block,
                user_society.id as user_society_id,user_society.society_id,user_society.status,user_society.commitee_member,user_society.relation
                from user_society
               inner join users on user_society.user_id = users.id
               inner join flat on flat.id = user_society.flat_id
                inner join block on block.id = user_society.block_id
                where user_society.id=:id limit 1",['id'=>$id]);
        if (!$post)
            return ['msg'=>'user doesnot exist with id - '.$id];
        return $post;

    }

    public function create() {
        $attributes = \Input::all();
//        print_r($attributes);exit;
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $society_id = $oauthToken->society_id;

        $validator = \Validator::make(
                                    $attributes,
                                    array(
                                            'first_name' => 'required|min:2',
                                            'email'=>'required|email',
                                            'flat_no'=>'required'
                                        )
                                    );
        if ($validator->fails()){

            return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
        }

        $flat_no 	= $attributes['flat_no'];
        $block_id 	= isset($attributes['block_id']) ? $attributes['block_id'] : null;
        $building 	= Building::find($attributes['building_id']);

        if (!$building) {
        	return ['msg'=>'building doesnt exist - '.$attributes['building_id'],'success'=>false];
        }

        $attributes['society_id'] = $society_id;

        $society = Society::find($attributes['society_id']);
        if (!$society)
            return ['msg'=>'society with id - '.$attributes['society_id'].' does not exist','success'=>false];

        if ($block_id) {
        	$block = Block::find($block_id);

        	if (!$block)
        		return ['msg'=>'block with id - '.$attributes['block_id'].' does not exist','success'=>false];

        }


        // flat validation
        $attrs = ['building_id'=>$attributes['building_id'],'flat_no'=>(int)$flat_no];

        if ($block_id) {

        	$blocksql = ' = :block_id ';
        	$attrs['block_id']=$block_id;

        } else {

        	$blocksql = ' is null ';
        }


//        $sql = 'select user_society.relation,flat.id from user_society inner join flat on user_society.flat_id = flat.id
//        		where user_society.building_id = :building_id and user_society.block_id '.$blocksql.' and flat.flat_no = :flat_no
//        		and user_society.status=1';
//
//        $result = \DB::selectOne($sql,$attrs);
//
//        if ($result) {
//        	if ($result->relation == $attributes['role']) {
//        		return ['success'=>false,'flat_error'=>'This flat is already taken with occupancy '.$attributes['role']];
//        	}
//
//        	$flat = Flat::find($result->id,['id']);
//
//        } else {
        $sql = 'select user_society.relation,flat.id from user_society inner join flat on user_society.flat_id = flat.id
        		where user_society.building_id = :building_id and user_society.block_id '.$blocksql.' and flat.flat_no = :flat_no
        		and user_society.status=1';
        //dd($sql);
        $new_result = \DB::select($sql,$attrs);
//            }
        //dd($new_result);
        //print_r($new_result->total);exit;
        if ($new_result) {

            $relations = [];
            foreach ($new_result as $k=>$v) {
                $relations[] = $new_result[$k]->relation;
            }

//            dd($relations);


            if (in_array($attributes['role'], $relations)) {
                return ['success'=>false,'flat_error'=>'This flat is already taken with occupancy '.$attributes['role']];

            }

            $flat = Flat::find($new_result[0]->id,['id']);
        }else{
        	$flat = new Flat();
        	$flat->fill($attributes);

        }

        unset($attributes['block_id']);

        if(!$flat)
        	return ['msg'=>'Flat not found error','success'=>false];



        if ($block_id)
        	$flat->block()->associate($block);

        $flat->save();

        // To check email is already registered
        $user = User::where('email','=',$attributes['email'])->first(['id','first_name', 'last_name', 'email']);
        if(!$user){
            $user = new User();
            $user->fill($attributes);
            $password = str_random(8);
            $user->password = bcrypt($password);
            $user->save();
        }

        $userWithSameFlat = UserSociety::where('user_id',$user->id)->where('flat_id',$flat->id)->first(['id']);

        if($userWithSameFlat){
            return ['success'=>false,'flat_error'=>'This flat is already taken by same user '];
        }

        $userSociety = new UserSociety();
        $userSociety->user()->associate($user);
        $userSociety->society()->associate($society);
        $userSociety->building()->associate($building);

        if($block_id)
        	$userSociety->block()->associate($block);

        $userSociety->flat()->associate($flat);
        $userSociety->relation = $attributes['role'];
        $userSociety->status = 1;
        $userSociety->save();
        if($userSociety==null)
        {
                return ['msg'=>'error','success'=>false];
        }else{

                $aclRole = AclRole::where('society_id','=',$society_id)->where('role_name','=','Member')->first();

                if (!$aclRole){
                    return ['msg'=>'Role does not exist','success'=>false];
                }
                else{
                    $aclUserRole = new AclUserRole();
                    $aclUserRole->user()->associate($user);
                    $aclUserRole->aclRole()->associate($aclRole);
                    $aclUserRole->save();
                }

                UserSociety::where('society_id','=',$society->id)->where('user_id','=',$user->id)->whereNull('block_id')->whereNull('flat_id')->whereNull('building_id')->delete();

                $data = array(
                         'name'=>$user->first_name.' '.$user->last_name,
                         'email'=>$user->email,
                         'society_name'=>$society->name,
                         'password'=>(!empty($password)) ? $password : 'Your account is already exist.Please use your previous password for login.'
                );

                		event(new \ApartmentApi\Events\UserWasCreated($data));
                return ['msg'=>'user created successfully','success'=>true];
        }
    }

    public function update($id) {

        $attributes = \Input::all();
//        $commitee_member = $attributes['commitee_member'];
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $society_id = $oauthToken->society_id;
        $user = User::find($id);

         if (!$user)
            return ['msg'=>'user with id - '.$id.' does not exist','success'=>false];

        $registeredUser = User::where('email','=',$attributes['email'])->first(['id','first_name', 'last_name', 'email']);
        if(($registeredUser) && ($registeredUser->email != $user->email)){
           return ['msg'=>'Email address is already registered user','success'=>false];
        }
        $user->fill($attributes);
        $user->save();



        return ['msg'=>'User details updated','success'=>true];

    }

    public function updateOld($id) {
        $attributes = \Input::all();
        $flat_no 	= $attributes['flat_no'];
        $block_id 	= $attributes['block_id'];
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $society_id = $oauthToken->society_id;
        $attributes['society_id'] = $society_id;
        $status = $attributes['status'];
        $commitee_member = $attributes['commitee_member'];
        $block = \ApartmentApi\Models\Block::find($attributes['block_id']);
        if (!$block)
            return ['msg'=>'block with id - '.$attributes['block_id'].' does not exist','success'=>false];
        $validator = \Validator::make(
                                    $attributes,
                                    array(
                                            'first_name' => 'required|min:2',
                                            'email'=>'required|email',
                                            'block_id'=>'required',
                                            'flat_no'=>'required'
                                        )
                                    );

        if ($validator->fails()){
            return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
        }
        $sql = 'select count(*) as total from flat where block_id = :block_id and flat_no = :flat_no';
        $result = \DB::selectOne($sql,['block_id'=>$block_id,'flat_no'=>$flat_no]);
        if($result->total > 1){
            return ['success'=>false,'flat_error'=>'This flat is already taken'];
        }

//		unset($attributes['flat_no']);
        unset($attributes['block_id']);
         unset($attributes['commitee_member']);

//        $role = AclRole::find($attributes['member_role']);
//        print_r($role);exit;
//        if (!$role)
//            return ['msg'=>'Role does not exist','success'=>false];
//
        $user = User::find($id);
        $user->fill($attributes);
        $user->save();
//       if($role=='owner')
//        {
//            \DB::table('acl_user_role')->where('user_id', '=', $id)->where('society_id', '=' , $society_id)->where('role_acl_name','=','tenant')->delete();
//        }else{
//            \DB::table('acl_user_role')->where('user_id', '=', $id)->where('society_id', '=' , $society_id)->where('role_acl_name','=','owner')->delete();
//        }
//        $aclUserRole = new AclUserRole();
//        $aclUserRole->user_id = $id;
//        $aclUserRole->role_acl_name = $attributes['member_role'];
//        $aclUserRole->society_id = $society_id;
//        $aclUserRole->save();

        $userSociety = UserSociety::where('id','=',$id)->firstOrFail();
        $flat_id = $userSociety['flat_id'];
//		$userSociety->user()->associate($user);
//		$userSociety->society()->associate($society);
        $userSociety->block()->associate($block);
//        	$userSociety->flat()->associate($flat);
        $userSociety->relation = $attributes['role'];
         $userSociety->commitee_member = $commitee_member;
        $userSociety->status =  $status;
//        print_r($userSociety);exit;
        $userSociety->save();

        $flat = Flat::find($flat_id);
        $flat->fill($attributes);
////		$flat->society()->associate($attributes);
        $flat->block()->associate($block);
        $flat->save();

        if($userSociety==null)
        {
            return ['msg'=>'error','success'=>false];
        }else{
            return ['msg'=>'user updated successfully','success'=>true];
        }
    }

    public function deactivate() {

        $attributes = \Input::all();
        $id = $attributes['id'];
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $society_id = $oauthToken->society_id;

        $userSociety = UserSociety::where('id','=',$id)->first();
        $flat_parking = FlatParking::where('flat_id','=',$userSociety->flat_id)->first();
         if (!$userSociety){
             return ['msg'=>'user with id - '.$id.' does not exist','success'=>false];
         } else if($flat_parking){
             return ['success'=>false,'msg'=>'Remove parking slot of this flat firstly!'];
         }else{
            $sql = 'select count(*) as total from acl_user_role where acl_role_id IN ( select id from acl_role where society_id = :society_id) and user_id = :user_id';
            $result = \DB::selectOne($sql,['society_id'=>$society_id,'user_id'=>$userSociety->user_id]);
            if($result->total){
                return ['success'=>false,'msg'=>'Roles is assigned to this user please remove that.'];
            }else{
                $userSociety->status = 0;
                $userSociety->save();
                return ['msg'=>'User de-activated successfully','success'=>true];
             }

         }

    }

    public function activate() {

        $attributes = \Input::all();
                $id = $attributes['id'];
//        $oauthToken = OauthToken::find(\Input::get('access_token'));
//        $society_id = $oauthToken->society_id;
        $userSociety = UserSociety::where('id','=',$id)->first();
        $flat = Flat::find($userSociety->flat_id);
         if (!$userSociety)
            return ['msg'=>'user with id - '.$id.' does not exist','success'=>false];

         $sql = 'select count(user_society.id) as total from user_society join flat on user_society.flat_id = flat.id
        		where user_society.building_id = :building_id and user_society.block_id = :block_id
				and flat.flat_no = :flat_no and user_society.relation = :relation and user_society.status = 1';

		$result = \DB::selectOne($sql,[
				'block_id'		=>	$userSociety->block_id ? $userSociety->block_id : null,
				'flat_no'		=>	$flat->flat_no,
				'relation'		=>	$userSociety->relation,
				'building_id'	=>	$userSociety->building_id,
		]);

		if($result->total){
//			return "false";
            return ['msg'=>'Flat is already taken with this occupancy!','success'=>false];
//		else
//			return "true";
//
//        $flat = UserSociety::where('id', '!=', $userSociety->id)->where('society_id', '=', $userSociety->society_id)->where('block_id', '=', $userSociety->block_id)->where('flat_id', '=', $userSociety->flat_id)->where('relation', '=', $userSociety->relation)->where('building_id', '=' ,$userSociety->building_id )->where('status', '=', 1)->first();
//
//        if($flat){
//            return ['msg'=>'Flat is already taken with this occupancy','success'=>false];
        } else {
          $userSociety->status = 1;
          $userSociety->save();
          return ['msg'=>'User activated successfully!','success'=>true];
       }


    }

    public function approve() {

        $attributes = \Input::all();
        $id = $attributes['id'];
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $society_id = $oauthToken->society_id;
        $userSociety = UserSociety::where('id','=',$id)->first();
        $flat = Flat::find($userSociety->flat_id);
         if (!$userSociety)
            return ['msg'=>'user with id - '.$id.' does not exist','success'=>false];

         $sql = 'select count(user_society.id) as total from user_society join flat on user_society.flat_id = flat.id
        		where user_society.building_id = :building_id and user_society.block_id = :block_id
				and flat.flat_no = :flat_no and user_society.relation = :relation and user_society.status = 1';

		$result = \DB::selectOne($sql,[
				'block_id'		=>	$userSociety->block_id ? $userSociety->block_id : null,
				'flat_no'		=>	$flat->flat_no,
				'relation'		=>	$userSociety->relation,
				'building_id'	=>	$userSociety->building_id,
		]);

		if($result->total){
//			return "false";
            return ['msg'=>'Flat is already taken with this occupancy!','success'=>false];

//         if (!$userSociety)
//            return ['msg'=>'user with id - '.$id.' does not exist','success'=>false];
//
//        $flat = UserSociety::where('id', '!=', $userSociety->id)->where('society_id', '=', $userSociety->society_id)->where('block_id', '=', $userSociety->block_id)->where('flat_id', '=', $userSociety->flat_id)->where('relation', '=', $userSociety->relation)->where('status', '=', 1)->first();
//        if($flat){
//            return ['msg'=>'Flat is already taken with this occupancy','success'=>false];
        } else {
            $userSociety->status = 1;
            $userSociety->save();

            $aclRole = AclRole::where('society_id','=',$society_id)->where('role_name','=','Member')->first();

            if (!$aclRole){
                return ['msg'=>'Role does not exist','success'=>false];
            }
            else{
                $aclUserRole = AclUserRole::where('user_id','=',$userSociety->user_id)->where('acl_role_id','=',$aclRole->id)->first();
                if(!$aclUserRole){
                    $aclUserRole = new AclUserRole();
                    $aclUserRole->user_id = $userSociety->user_id;
                    $aclUserRole->aclRole()->associate($aclRole);
                    $aclUserRole->save();
                }

            }

          return ['msg'=>'User approved successfully','success'=>true];
       }

    }

    public function getSocietyUsers(){
//        $oauthToken = OauthToken::find(\Input::get('access_token'));
//        $society_id = $oauthToken->society_id;
//        $user = $oauthToken->user()->first();
//
//        $users = $user->getRoleUsers('commitee_member',$society_id);
//        $users = $user->getSocietyUsers($society_id);

        $meetingData = array(
                            'title'=>'Meeting title',
                            'created_by'=>'Neha Agrawal',
                            'venue'=>'SportsClub',
                            'notes'=>'Meeting notes',
                            'agenda'=>'Meeting agenda',
                            'meeting_on'=>'2015-09-10 10:00AM',
                            'invitees'=>'M',
                            'society_id'=>84
                        );
            event(new \ApartmentApi\Events\MeetingWasCreated($meetingData));

        return ['msg'=>'user fetched successfully','success'=>true];
    }

    /*
     * check the old password field from database
     * @Neha
     */
    public function check_pwd()
    {
//        print_r("hello");exit;
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $old_password = $_POST['old_password'];
        $email_id = $_POST['email_id'];
      if(\Auth::validate(['email' => $email_id, 'password' => $old_password,]))
      {
           return ['success'=>true,'msg'=>'old_password is correct'];
      }else{
           return ['success'=>false,'msg'=>'Please enter correct password'];
      }

    }
    /*
     * Update the old password with new password
     */
    public function update_pwd()
    {

        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $user_details = $oauthToken->user()->first();
        $email_id = $user_details->email;
        $attributes = \Input::all();
//        print_r($attributes);exit;
        $new_password = $attributes['new_password'];
        $confirm_password = $attributes['confirm_password'];
        $username = $attributes['username'];
        $old_pwd = $attributes['old_password'];
        $valid = \Auth::validate(['email' => $username, 'password' => $old_pwd,]);
           if(! $valid)
           {
               return ['success'=>false,'incorrect_pwd'=>'incorrect','msg'=>'Please enter correct password'];
            }
            if($new_password == $old_pwd)
            {
                return ['success'=>false,'msg'=>'New Password cannot be as same as old password'];
            }
             if($confirm_password != $new_password)
            {
                return ['success'=>false,'msg'=>'New password does not match with confirm password!.'];
            }
            else{
                $new_pwd = $attributes['new_password'];
                $user = User::where('email','=',$email_id)->first();
                $user->password = bcrypt($new_pwd);

                $user->save();
                if($user->save())
                {
                    return ['success'=>true,'msg'=>'Password updated successfully'];
                }else{
                    return ['success'=>false,'msg'=>'Some error occured'];
                }
            }

    }

    public function reset_forgotPwd()
	{
//        print_r("hi");exit;
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $user_details = $oauthToken->user()->first();
        $email_id = $user_details->email;
        $attributes = \Input::all();
//        $username = $attributes['username'];
//        $old_pwd = $attributes['old_password'];
//        $valid = \Auth::validate(['email' => $username, 'password' => $old_pwd,]);
//           if(! $valid)
//           {
//               return ['success'=>false,'incorrect_pwd'=>'incorrect','msg'=>'Please enter correct password'];
//            }else{
                $new_pwd = $attributes['new_password'];
                $user = User::where('email','=',$email_id)->first();
                $user->password = bcrypt($new_pwd);
                $user->password_changed = '1';

                $user->save();
                if($user->save())
                {
                    return ['success'=>true,'msg'=>'Password updated successfully'];
                }else{
                    return ['success'=>false,'msg'=>'Some error occured'];
                }
//            }
	}
        public function getAllUsers()
    {
//            print_r("hello");exit;
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
        $data = \DB::select("select users.id,users.first_name,users.last_name,users.email,
                user_society.id as user_society_id,user_society.society_id
                from user_society
               inner join users on user_society.user_id = users.id
               where  user_society.status = 1 and user_society.society_id = :society_id group by user_society.user_id,user_society.society_id ORDER BY users.first_name,users.last_name",['society_id'=>$society_id]);
//                where user_society.society_id=:id",['id'=>$id]);
        if (!$data)
            return ['msg'=>'user doesnot exist with id '.$society_id];

//         $count = \DB::selectOne(
//                    "select count(users.id) total
//                    from user_society
//                    inner join users on user_society.user_id = users.id
//                     where  user_society.status = 1 and user_society.society_id = :society_id group by user_society.user_id,user_society.society_id",['society_id'=>$society_id]);
            return array(
//                'total'=>$count->total,
                'data'=>$data
            );
//        return $post;

    }
    /** get data of user which is logged in**/
    public function getUser_info()
    {

        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $user_id = $oauthToken->user_id;
//        print_r($user_id);exit;
        $data = \DB::selectOne("select * from users where id = :user_id",['user_id'=>$user_id]);
        return array(
                'data'=>$data,
            );
    }
    /** update data of logged in user **/
    public function editUser_info($id)
    {
        $attributes = \Input::all();
//        print_r($attributes);exit;
        $user = User::where('id','=',$id)
				->firstOrFail();
         if (!$user)
                    return ['error'=>'user not found with id: '.$id,'success'=>false];
        $user->fill($attributes);
        $user->save();
        
        $flat_id = UserSociety::where('user_id','=',$id)->where('relation','=','associate')->first();
        if($flat_id)
        {
            $member = Member::where('flat_id','=',$flat_id)->where('associate_member','=','1')->first();
//            $member->name = $attributes['first_name'].' '.$attributes['last_name'];
//            $member->contact_number = $attributes['contact_no'];
            $member->fill($attributes);
            $member->save();
        }
        return ['msg'=>'User updated successfully','success'=>true];
    }

}
