<?php
namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\Block;
use ApartmentApi\Models\Building;
use ApartmentApi\Models\Flat;
use ApartmentApi\Models\Member;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\Society;
use ApartmentApi\Models\User;
use ApartmentApi\Models\UserSociety;
use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\AclUserRole;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Symfony\Component\HttpFoundation\Response;

class FlatController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('rest',[
            'except' =>
                ['updateFlat']
            ]);
	}

        /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

        public function getFlat($id) {

		$flat = Flat::find($id);
		if (!$flat)
			return ['msg'=>'Flat doesnot exist with id - '.$id];

		return ['msg'=>'Successfully fetched flat','data'=>$flat,'success'=>true];
	}

    /**
	 * Get the listing of user flats.
	 *
	 * @return Response
	 */
    public function getFlats()
    {
        $oauthToken = OauthToken::find(\Input::get('access_token'));
                $society_id = $oauthToken->society_id;
                $user_id = $oauthToken->user_id;
//                $user = $oauthToken->user()->first();

		$sql = <<<EOF

		select user_society.id as user_society_id,society.name as building_name,flat.id as flat_id,flat.flat_no,user_society.relation,block.id as block_id,block.block from user_society
				inner join flat on flat.id = user_society.flat_id
                inner join building on building.id = user_society.building_id
                inner join society on user_society.building_id = society.id
                left join block on block.id = user_society.block_id
				where user_society.society_id = :society_id and user_society.user_id = :user_id and user_society.status = 1 group by flat.id

EOF;

		$flats =  DB::select($sql, ['society_id'=>$society_id,'user_id'=>$user_id]);

//        print_r($flats);exit;
                return ['msg'=>'Successfully fetched flats','data'=>$flats,'success'=>true];
	}

        /**
	 * Get the listing of user flats.
	 *
	 * @return Response
	 */

        public function getUserFlats($id) {

                $oauthToken = OauthToken::find(\Input::get('access_token'));
                $society_id = $oauthToken->society_id;
                $user_id = $id;
//                $user = $oauthToken->user()->first();

		$sql = <<<EOF

		select user_society.id as user_society_id,society.name as building_name,flat.id as flat_id,
                flat.flat_no,flat.type,user_society.relation,user_society.status as status,
                block.id as block_id,block.block from user_society
                inner join flat on flat.id = user_society.flat_id
                inner join building on building.id = user_society.building_id
                inner join society on user_society.building_id = society.id
                left join block on block.id = user_society.block_id
				where user_society.society_id = :society_id and user_society.user_id = :user_id

EOF;

		$flats =  DB::select($sql, ['society_id'=>$society_id,'user_id'=>$user_id]);

//        print_r($flats);exit;
                return ['msg'=>'Successfully fetched flats','data'=>$flats,'success'=>true];


	}

    public function getUserFlat($id) {

                $oauthToken = OauthToken::find(\Input::get('access_token'));
                $society_id = $oauthToken->society_id;
                $user_id = $oauthToken->user_id;
//                $user = $oauthToken->user()->first();

		$sql = <<<EOF

		select user_society.id as user_society_id,user_society.user_id,society.name as building_name,society.id as building_id,flat.id as flat_id,flat.flat_no,flat.type,
                    user_society.relation,block.id as block_id,block.block from user_society
		inner join flat on flat.id = user_society.flat_id
                inner join building on building.id = user_society.building_id
                inner join society on user_society.building_id = society.id
                left join block on block.id = user_society.block_id
				where user_society.id = :user_society_id

EOF;

		$flat =  DB::selectOne($sql, ['user_society_id'=>$id]);


                return ['msg'=>'Successfully fetched flat','data'=>$flat,'success'=>true];


	}

	public function members($id)
	{
		$members = DB::select("select member.*,category.name as relation from member"
                        . " LEFT JOIN category on member.relation_id = category.id"
                        . " where flat_id=".$id);
//                print_r($members);exit;
		return ['data'=>$members];
	}

	/**
	 * Create and update members.
	 * @return Response
	 */
	public function createMember1(Request $request)
	{

		$attributes = \Input::all();
                $validator = \Validator::make(
                    $attributes,
                            array(
                                'name' => 'Required',
                                'email'=>'Between:3,64|Email',
                                'contact_number'=>'numeric', 'digits_between:8,25',
                                'unique_id'=> '',
                                'voter_id'=> ''
                            ));

                if ($validator->fails()){
                    return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];
                }
                $oauthToken = OauthToken::find(\Input::get('access_token'));
//                $user_id = $oauthToken->user()->first();
                $flat_id = $attributes['flat_id'];
                $society = $oauthToken->society()->first();
                $user = $oauthToken->user()->first();
                if(isset($attributes['status']))
                {
                    if($attributes['status']=='1')
                    {

                        $flat = Flat::find($flat_id,['id','flat_no']);
                        if (!$flat)
                        {
                            return ['msg'=>'Flat doesnot exist with id '.$flat_id,'success'=>false];
                        }
                        $validator = \Validator::make(
                        $attributes,
                            array(
                                'name' => 'required',
                                'email'=>'between:3,64|email',
                                'contact_number'=>'numeric', 'digits_between:10,10',
                            )
                        );

                        if ($validator->fails()){
                            return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];
                        }
                        $user_society = UserSociety::where('flat_id',$flat_id)->where('relation','=','associate')->first();

        if($user_society){
//            print_r($user_society->user_id);exit;
            $user = User::findorfail($user_society->user_id);
//            print_r($user);exit;
            if($user->email != $attributes['email']){
               $exsitingUser = User::where('email','=',$attributes['email'])->first();
               if($exsitingUser){
                 return ['msg'=>'Error in updating associate memeber. Email id is already taken','success'=>false];
               }else{
                   $user->first_name = $attributes['name'];
                   $user->contact_no = $attributes['contact_number'];
                  $user->fill($attributes);
               }

            }else{
                $user->first_name = $attributes['name'];
                $user->contact_no = $attributes['contact_number'];
               $user->fill($attributes);
            }

            $user->save();
            return ['msg'=>'Associate member updated successfully','data'=>$user,'success'=>true];
        }else{
            // To check email is already registered
            $user = User::where('email','=',$attributes['email'])->first();
            if(!$user){
                $user = new User();
                $user->first_name = $attributes['name'];
                $user->contact_no = $attributes['contact_number'];
                $user->fill($attributes);
                $password = str_random(8);
                $user->password = bcrypt($password);
                $user->save();

            }

            $userFlat = UserSociety::where('flat_id',$flat_id)->first(['id','block_id','building_id']);

            $userSociety = new UserSociety();
            $userSociety->user_id = $user->id;
            $userSociety->society_id = $society->id;
            $userSociety->flat_id = $flat_id;
            $userSociety->block_id = $userFlat->block_id;
            $userSociety->building_id = $userFlat->building_id;
            $userSociety->relation = 'associate';
            $userSociety->status = 1;
            $userSociety->save();

        $associateRole = AclRole::where('role_name','Associate Member')->where('society_id',$society->id)->whereNull('parent_id')->first(['id']);
//        print_r($associateRole->id);exit;
        $aclUserRole = new AclUserRole();
        $aclUserRole->user()->associate($user);
//        $aclUserRole->aclRole()->associate($associateRole);
        $aclUserRole->acl_role_id = $associateRole->id;
        $aclUserRole->save();

        $block = $userSociety->block()->first(['block']);

        if($block){
           $block_name =  $block->block.'-';
        }else{
           $block_name = '';
        }

        $building = Society::find($userSociety->building_id,['name']);


        $flat = $building->name.'-'.$block_name.$flat->flat_no;
        if ($request->has('id')) {
                    $member = Member::findorfail($request->get('id'));
                }
                else {
                $member = new Member();
                }
                if(!$member)
                {
                    return ['msg'=>'doesnot exist','success'=>true ];
                }

                $member->fill($attributes);
                $member->save();

//        $data = array(
//                         'name'=>$user->first_name.' '.$user->last_name,
//                         'email'=>$user->email,
//                         'society_name'=>$society->name,
//                         'flat_no'=>$flat,
//                         'password'=>(!empty($password)) ? $password : 'Your account is already exist.Please use your previous password for login.'
//                );
//
//        event(new \ApartmentApi\Events\AssociateMemberWasAdded($data));

            return ['msg'=>'Associate member added successfully','data'=>$user,'success'=>true];
        }

                    }
                }else{

                if ($request->has('id')) {
                    $member = Member::findorfail($request->get('id'));
                }
                else {
                $member = new Member();
                }
                if(!$member)
                {
                    return ['msg'=>'doesnot exist','success'=>true ];
                }

                $member->fill($attributes);
                $member->save();

                return ['msg'=>' updated successfully','data'=>$member,'success'=>true] ;
                }
	}

        /**
	 * Delete members.
	 * @return Response
	 */
        public function getMember($id) {

            $member = Member::Leftjoin('category','member.relation_id','=','category.id')
                    ->select('member.*','category.id as category_id','category.name as relation')
                    ->where ('member.id','=',$id)
                    ->first();
//            $data = Member::find($id);
//            print_r($data);exit;
//            $associate = UserSociety::where('flat_id','=',$data->flat_id)->where('relation','=','associate')->first();
//
            if (!$member)
			return ['msg'=>'Member doesnot exist with id - '.$id];

            return ['member'=>$member];

        }

	public function deleteMember()
	{
            $data = \Input::all();
            $member = Member::find($data['member_id']);
            $user_society = UserSociety::where('flat_id',$member->flat_id)->where('relation','=','associate')->first();
            if($user_society)
            {
                $user = User::where('id','=',$user_society->user_id)->delete();
                $user_society->delete();
                $member->delete();
            }else{
                $member->delete();
            }

            return ['msg'=>'Member deleted successfully'];
        }

        public function updateFlat($id)
        {

            $attributes = \Input::all();

//            $validator = \Validator::make(
//                $attributes,
//                array('title' => 'required','venue'=>'required')
//            );
//            if ($validator->fails())
//                return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
            $flat = Flat::find($id);

            if(!$flat)
            {
             return ['msg'=>'Flat doesnot exist with id - '.$id];
            }
            $flat->fill($attributes);
//             print_r($flat);exit;
            $flat->save();

            return ['msg'=>'Flat updated successfully'];

        }

        public function updateFlats()
        {
            $data = \Input::all();

//            $validator = \Validator::make(
//                $attributes,
//                array('title' => 'required','venue'=>'required')
//            );
//            if ($validator->fails())
//                return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
            $error = false;
            $flat_error_ids = array();
            foreach($data['flat'] as $id=>$flat){

                $userSociety =  UserSociety::find($id);
                $userSociety->block_id = $flat['block_id'];
                $userSociety->relation = $flat['relation'];


                $sql = 'select count(*) as total from user_society join flat on user_society.flat_id = flat.id where user_society.block_id = :block_id and flat.flat_no = :flat_no and user_society.relation = :relation and flat.id != :flat_id';
                $result = DB::selectOne($sql,['block_id'=>$flat['block_id'],'flat_no'=>$flat['flat_no'],'flat_id'=>$flat['flat_id'],'relation'=>$userSociety->relation]);
                if($result->total){
                    $error = true;
                    $flat_error_ids[] = $flat['flat_id'];
                }else{
                    $fl = Flat::find($flat['flat_id']);
                    $fl->flat_no = $flat['flat_no'];
                    $fl->save();
                    $userSociety->save();
                }


            }

            if($error){
                return ['success'=>false,'msg'=>'Flat is already taken','flat_ids'=>$flat_error_ids];
            }else{
               return ['msg'=>'Flats updated successfully','success'=>true];
            }



        }

        public function addAdminFlat(Request $request)
        {
            $oauthToken = OauthToken::find($request->get('access_token'));
            $user = $oauthToken->user()->first();
            $society = Society::find($oauthToken->society_id);
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
                $userSociety->status = 1;
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
                $userSociety->status = 1;
                $userSociety->save();
            }

            UserSociety::where('society_id','=',$society->id)->where('user_id','=',$oauthToken->user_id)->whereNull('block_id')->whereNull('flat_id')->whereNull('building_id')->delete();

            $data = ['flat_id'=>$flat->id];
            return ['msg'=>'Successfully saved flat','data'=>$data,'success'=>true];

        }

        public function updateUserFlat($id,Request $request){

//            print_r($id);exit;
            $building = Building::find($request->get('building_id'));

            $attrs = ['building_id'=>$request->get('building_id'),'id'=>$id,'flat_no'=>(int)$request->get('flat_no'),'relation'=>$request->get('relation'),];

            if ($request->get('block_id',NULL)) {

                $blocksql = ' = :block_id ';
                $attrs['block_id']=$request->get('block_id');

            } else {

                $blocksql = ' is null ';
            }


             $sql = 'select user_society.relation,flat.id from user_society inner join flat on user_society.flat_id = flat.id
        		where user_society.building_id = :building_id and user_society.block_id '.$blocksql.' and flat.flat_no = :flat_no and user_society.id != :id
        		and user_society.status=1 and user_society.relation = :relation';

            $new_result = DB::select($sql,$attrs);

             if ($new_result) {

            $relations = [];
            foreach ($new_result as $k=>$v) {
                $relations[] = $new_result[$k]->relation;
            }

            //dd($relations);


            if (in_array($request->get('relation'), $relations)) {
//                dd("hi");
                return ['success'=>false,'flat_error'=>'This flat is already taken with occupancy '.$request->get('relation')];

            }

            $flat = Flat::find($new_result[0]->id,['id']);

            } else{
//            print_r($id);exit;
            $flat = Flat::find($request->get('flat_id'));
            }


            $flat->flat_no = $request->get('flat_no');
            $flat->type = $request->get('type');
            $block_id = $request->get('block_id');
            $flat_no = $request->get('flat_no');
            $relation = $request->get('relation');
//            print_r($block_id);print_r($relation);exit;


            $flat->save();
            $userSociety =  UserSociety::find($id);

            $userSociety->building()->associate($building);
            if($request->has('block_id')){
                $block = Block::find($request->get('block_id'));
                $userSociety->block()->associate($block);
            }else{
                $userSociety->block_id = NULL;
            }
            $userSociety->flat()->associate($flat);
            $userSociety->relation = $request->get('relation');
            $userSociety->save();

            return ['msg'=>'Flat updated successfully!','success'=>true];


        }

        public function AddUserFlat()
        {
            $attributes = Input::all();
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;

//         $sql = 'select count(user_society.id) count from user_society
//         		inner join users on users.id = user_society.user_id
//         		where users.email = :email and user_society.society_id = :society_id';

//         $count = DB::selectOne($sql,['email'=>$attributes['email'],'society_id'=>$society_id])->count;

//         if ($count > 0) {
//         	return ['msg'=>'provided email already used by the society\'s admin. Please provide differnt email.'];
//         }

        $validator = \Validator::make(
                                    $attributes,
                                    array(
                                            'building_id'=>'required',
                                            'flat_no'=>'required',
                                            'type'=>'required'
                                        )
                                    );
        if ($validator->fails()){

            return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
        }

        $flat_no = $attributes['flat_no'];
        $flat_type = $attributes['type'];
        $block_id = isset($attributes['block_id']) ? $attributes['block_id'] : null;
        $building = Building::find($attributes['building_id']);


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
//        $attrs = ['building_id'=>$attributes['building_id'],'relation'=>$attributes['role'],'flat_no'=>(int)$flat_no];
        $attr = ['building_id'=>$attributes['building_id'],'flat_no'=>(int)$flat_no];

        if ($block_id) {

        	$blocksql = ' = :block_id ';
        	$attr['block_id']=$block_id;

        } else {

        	$blocksql = ' is null ';
        }


//        $sql = 'select user_society.relation,flat.id from user_society inner join flat on user_society.flat_id = flat.id
//        		where user_society.building_id = :building_id and user_society.block_id '.$blocksql.' and flat.flat_no = :flat_no
//        		and user_society.status=1';
//        $sql = 'select count(*) as total from user_society inner join flat on user_society.flat_id = flat.id
//        		where user_society.building_id = :building_id and user_society.block_id '.$blocksql.' and flat.flat_no = :flat_no and user_society.relation = :relation
//                and user_society.status=1';
//
//        $result = DB::selectOne($sql,$attrs);
//        print_r($result);
//        print"................";
//        print_r($attributes['role']);exit;

//        if ($result->total) {
////        	if ($result->relation == $attributes['role']) {
//        		return ['success'=>false,'flat_error'=>'This flat is already taken with occupancy '.$attributes['role']];
//        	}

          $sql = 'select user_society.relation,flat.id from user_society inner join flat on user_society.flat_id = flat.id
        		where user_society.building_id = :building_id and user_society.block_id '.$blocksql.' and flat.flat_no = :flat_no
        		and user_society.status=1';
        //dd($sql);
        $new_result = DB::select($sql,$attr);
//            }
        //dd($new_result);
        //print_r($new_result->total);exit;
        if ($new_result) {

            $relations = [];
            foreach ($new_result as $k=>$v) {
                $relations[] = $new_result[$k]->relation;
            }

            //dd($relations);


            if (in_array($attributes['role'], $relations)) {
                return ['success'=>false,'flat_error'=>'This flat is already taken with occupancy '.$attributes['role']];

            }

            $flat = Flat::find($new_result[0]->id,['id']);

        } else {
        	$flat = new Flat();
        	$flat->fill($attributes);
        }

        //dd($flat);

        unset($attributes['block_id']);

        if(!$flat)
        	return ['msg'=>'Flat not found error','success'=>false];

        if ($block_id)
        	$flat->block()->associate($block);

        $flat->save();

        // To check email is already registered
//        $user = User::where('email','=',$attributes['email'])->first(['id','first_name', 'last_name', 'email']);
//        if(!$user){
//            $user = new User();
//            $user->fill($attributes);
//            $password = str_random(8);
//            $user->password = bcrypt($password);
//            $user->save();
//        }

        $userSociety = new UserSociety();
//        $userSociety->user()->associate($user);
        $userSociety->society()->associate($society);
        $userSociety->building()->associate($building);
        $userSociety->user_id = $attributes['user_id'];

        if($block_id)
        	$userSociety->block()->associate($block);

        $userSociety->flat()->associate($flat);
        $userSociety->relation = $attributes['role'];
        $userSociety->status = 1;
        $userSociety->save();
        return ['msg'=>'flat created successfully!','success'=>true];
//        if($userSociety==null)
//        {
//                return ['msg'=>'error','success'=>false];
//        }else{
//
//                $aclRole = AclRole::where('society_id','=',$society_id)->where('role_name','=','Member')->first();
//
//                if (!$aclRole){
//                    return ['msg'=>'Role does not exist','success'=>false];
//                }
//                else{
//                    $aclUserRole = new AclUserRole();
//                    $aclUserRole->user()->associate($user);
//                    $aclUserRole->aclRole()->associate($aclRole);
//                    $aclUserRole->save();
//                }
//
//                $data = array(
//                         'name'=>$user->first_name.' '.$user->last_name,
//                         'email'=>$user->email,
//                         'password'=>(!empty($password)) ? $password : 'Your account is already exist.Please use your previous password for login.'
//                );
//
//                		event(new \ApartmentApi\Events\UserWasCreated($data));
//                return ['msg'=>'user created successfully','success'=>true];
//        }
        }

    /**
	 * Create and update associate member.
	 * @return Response
	 */
	public function createAssociateMember(Request $request)
	{
        $oauthToken = OauthToken::find($request->get('access_token'));
        $society = $oauthToken->society()->first();
        $user = $oauthToken->user()->first();
		$attributes = $request->all();
        $flat_id = $attributes['flat_id'];

        $flat = Flat::find($flat_id,['id','flat_no']);
        if (!$flat)
            return ['msg'=>'Flat doesnot exist with id '.$flat_id,'success'=>false];

        $validator = \Validator::make(
            $attributes,
                    array(
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'email'=>'between:3,64|email',
                        'contact_number'=>'numeric', 'digits_between:10,10',
                    )
        );

        if ($validator->fails()){
            return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];
        }

        if ($request->has('id')) {
            $user = User::findorfail($request->get('id'));
            if($user->email != $attributes['email']){
               $exsitingUser = User::where('email','=',$attributes['email'])->first();
               if($exsitingUser){
                 return ['msg'=>'Error in updating associate memeber. Email id is already taken','success'=>false];
               }else{
                  $user->fill($attributes);
               }

            }else{
               $user->fill($attributes);
            }

            $user->save();
            return ['msg'=>'Associate member updated successfully','data'=>$user,'success'=>true];
        }else{
            // To check email is already registered
            $user = User::where('email','=',$attributes['email'])->first();
            if(!$user){
                $user = new User();
                $user->fill($attributes);
                $password = str_random(8);
                $user->password = bcrypt($password);
                $user->save();

            }

            $userFlat = UserSociety::where('flat_id',$flat_id)->first(['id','block_id','building_id']);

            $userSociety = new UserSociety();
            $userSociety->user_id = $user->id;
            $userSociety->society_id = $society->id;
            $userSociety->flat_id = $flat_id;
            $userSociety->block_id = $userFlat->block_id;
            $userSociety->building_id = $userFlat->building_id;
            $userSociety->relation = 'associate';
            $userSociety->status = 1;
            $userSociety->save();

        $associateRole = AclRole::where('role_name','Associate Member')->where('society_id',$society->id)->whereNull('parent_id')->first(['id']);
        $aclUserRole = new AclUserRole();
        $aclUserRole->user()->associate($user);
        $aclUserRole->aclRole()->associate($associateRole);
        $aclUserRole->save();

        $block = $userSociety->block()->first(['block']);

        if($block){
           $block_name =  $block->block.'-';
        }else{
           $block_name = '';
        }

        $building = Society::find($userSociety->building_id,['name']);


        $flat = $building->name.'-'.$block_name.$flat->flat_no;

        $data = array(
                         'name'=>$user->first_name.' '.$user->last_name,
                         'email'=>$user->email,
                         'society_name'=>$society->name,
                         'flat_no'=>$flat,
                         'password'=>(!empty($password)) ? $password : 'Your account is already exist.Please use your previous password for login.'
                );

        event(new \ApartmentApi\Events\AssociateMemberWasAdded($data));

            return ['msg'=>'Associate member added successfully','data'=>$user,'success'=>true];
        }
	}

    /*
     * Get Associate Member
     */
     public function getAssociateMember($id, Request $request)
     {
        $oauthToken = OauthToken::find($request->get('access_token'));
        $society = $oauthToken->society()->first();
        $user = $oauthToken->user()->first();
		$attributes = $request->all();

        $sql = "select users.* from user_society inner join users on user_society.user_id = users.id
        		where user_society.flat_id = :flat_id and user_society.relation ='associate'";

        $associateUser = DB::selectOne($sql,array('flat_id'=>$id));

		$count = \DB::selectOne(
				"select count(*) total from user_society inner join users on user_society.user_id = users.id
        		where user_society.flat_id = :flat_id and user_society.relation ='associate' ",
					array('flat_id'=>$id)
				);

        return ['msg'=>' Associate member fetched successfully','data'=>$associateUser,'success'=>true,'total'=>$count->total] ;

     }

     /*
     * Delete Associate Member
     */
     public function deleteAssociateMember($id, Request $request)
     {
        $oauthToken = OauthToken::find($request->get('access_token'));
        $society = $oauthToken->society()->first();

        $userSociety = UserSociety::where('flat_id',$id)->where('relation','associate')->first(['id','user_id']);

        UserSociety::where('flat_id',$id)->where('relation','associate')->first(['id'])->delete();

        $associateRole = AclRole::where('role_name','Associate Member')->where('society_id',$society->id)->whereNull('parent_id')->first(['id']);

//        AclUserRole::where('acl_role_id',$associateRole->id)->where('user_id',$userSociety->user_id)->delete();

        $sql = 'delete from acl_user_role where acl_role_id = :acl_role_id and user_id = :user_id limit 1';

        DB::delete($sql,['acl_role_id'=>$associateRole->id,'user_id'=>$userSociety->user_id]);

        return ['msg'=>' Associate member deleted successfully','success'=>true] ;

     }


     public function createMember(Request $request)
        {
            $attributes = \Input::all();
//            print_r($attributes);exit;
            $validator = \Validator::make(
                    $attributes,
                            array(
                                'first_name' => 'Required',
                                'email'=>'Between:3,64|Email',
                                'contact_no'=>'numeric', 'digits_between:8,25',
                                'unique_id'=> '',
                                'voter_id'=> ''
                            ));

                if ($validator->fails()){
                    return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];
                }
                $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
                $society_name = OauthToken::find(\Input::get('access_token'))->name;
                $user_id = $oauthToken = OauthToken::find(\Input::get('access_token'))->user_id;
                $flat_id = $attributes['flat_id'];
                if(isset($attributes['email']))
                {
                    if($attributes['email']!='')
                        {
                            $member_email = Member::where('email','=',$attributes['email'])->where('flat_id','=',$flat_id)->first();
                            if($member_email){
                                return ['msg'=>'Email id is already taken','already_exist'=>'already taken','success'=>false];
                            }
                            $isAdmin = User::where(['email' => $attributes['email']])
                                            ->whereHas('aclUserRole.aclRole', function($q) {
                                                $q->whereRoleName('Admin');
                                            })->count();

                            if ($isAdmin) {
                                return ['msg'=>'Email id is already registered as admin.','already_exist'=>'already taken','success'=>false];
                            }
                        }
                }
                 if(isset($attributes['associate_member']))
                {
                      if($attributes['associate_member']=='1')
                    {
                        $flat = Flat::find($flat_id,['id','flat_no']);
                        if (!$flat)
                        {
                            return ['msg'=>'Flat doesnot exist with id '.$flat_id,'success'=>false];
                        }
                        $validator = \Validator::make(
                        $attributes,
                            array(
                                'first_name' => 'required',
                                'email'=>'between:3,64|email',
                                'contact_no'=>'numeric', 'digits_between:10,10',
                            )
                        );

                        if ($validator->fails()){
                            return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];
                        }

                        $user_society = UserSociety::where('flat_id',$flat_id)->where('relation','=','associate')->first();

                        if(User::whereEmail($attributes['email'])->first()) {
                            return ['msg'=>'Email id is already taken.','already_exist'=>'already taken','success'=>false];
                        }

                        if($user_society)
                        {
                            $user_data = User::find($user_society->user_id);
                            if($user_data->email == $attributes['email'])
                            {
                                return ['msg'=>'Email id is already taken.','already_exist'=>'already taken','success'=>false];
                            }else{
                                $member_check = Member::where('flat_id','=',$flat_id)->where('associate_member','=',1)->first();
                                $member_check->associate_member = 0;
                                $member_check->save();
                                User::where('id','=',$user_society->user_id)->delete();
                                UserSociety::where('flat_id',$flat_id)->where('relation','=','associate')->delete();
                                AclUserRole::where('user_id','=',$user_society->user_id)->delete();
                            }
                        }

                            $user = new User();
                            $user->fill($attributes);
                            $password = str_random(8);
                            $user->password = bcrypt($password);
                            $user->save();

                            $userFlat = UserSociety::where('flat_id',$flat_id)->first(['id','block_id','building_id']);
                            $userSociety = new UserSociety();
                            $userSociety->user_id = $user->id;
                            $userSociety->society_id = $society_id;
                            $userSociety->flat_id = $flat_id;
                            $userSociety->block_id = $userFlat->block_id;
                            $userSociety->building_id = $userFlat->building_id;
                            $userSociety->relation = 'associate';
                            $userSociety->status = 1;
                            $userSociety->save();

                            $associateRole = AclRole::where('role_name','Associate Member')->where('society_id',$society_id)->whereNull('parent_id')->first(['id']);
                            $aclUserRole = new AclUserRole();
                            $aclUserRole->user_id = $userSociety->user_id;
                            $aclUserRole->acl_role_id = $associateRole->id;
//                            $aclUserRole->aclRole()->associate($associateRole);
                            $aclUserRole->save();

                            $member = new Member();
                            $member->fill($attributes);
                            $member->associate_member = 1;
                            $member->save();

                            $block = $userSociety->block()->first(['block']);

                            if($block){
                               $block_name =  $block->block.'-';
                            }else{
                               $block_name = '';
                            }

                            $building = Society::find($userSociety->building_id,['name']);

                            $flat = $building->name.'-'.$block_name.$flat->flat_no;

                            $data = array(
                                             'name'=>$user->first_name,
                                             'email'=>$user->email,
                                             'society_name'=>$society_name,
                                             'flat_no'=>$flat,
                                             'password'=>(!empty($password)) ? $password : 'Your account is already exist.Please use your previous password for login.'
                                    );

                             event(new \ApartmentApi\Events\AssociateMemberWasAdded($data));
                            return ['msg'=>' added successfully with associate member','success'=>true] ;
                    }
                }else{
                        if(isset($attributes['email']))
                        {
                            $data = Member::where('email','=',$attributes['email'])->where('associate_member','=','0')->first();
                            if($data)
                            {
                                return ['msg'=>'Email id is already taken','already_exist'=>'already taken','success'=>false];
                            }
                        }

                        $member = new Member();
                        $member->fill($attributes);
                        $member->save();
                        return ['msg'=>' added successfully','success'=>true] ;
                }
        }

        public function updateMember(Request $request,$id)
        {
            $attributes = $request->all();
//            print_r($attributes);exit;
            $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
            $society_name = OauthToken::find(\Input::get('access_token'))->name;
            $user_id = $oauthToken = OauthToken::find(\Input::get('access_token'))->user_id;
            $flat_id = $attributes['flat_id'];
            $member = Member::find($id);
             if(!$member)
            {
                return ['error'=>'Member not found with id: '.$id,'success'=>false];
            }

            if(isset($attributes['email']))
            {
                if($attributes['email']!='')
                {
                    $member_email = Member::where('email','=',$attributes['email'])->where('id','<>',$id)->first();
                    if($member_email)
                    {
                        return ['msg'=>'Email id is already taken','already_exist'=>'already taken','success'=>false];
                    }
                }
            }
            if($attributes['associate_member'] == $member->associate_member)
            {
                    if($attributes['associate_member']=='1')
                     {
                          $user_society = UserSociety::where('flat_id',$flat_id)->where('relation','=','associate')->first();
                           $user_email = User::where('email','=',$attributes['email'])->where('id','<>',$user_society->user_id)->first();
                            if($user_email){
                                return ['msg'=>'Email id is already taken','already_exist'=>'already taken','success'=>false];
                            }else{
                                $user = User::find($user_society->user_id);
                                $user->fill($attributes);
                                $user->save();
                            }
                            $member->fill($attributes);
                            $member->save();
                            return ['msg'=>' updated successfully with associate member','success'=>true];
                     }else{
                          if($attributes['associate_member']=='0')
                          {
                            $member->fill($attributes);
                            $member->save();
                            return ['msg'=>' updated successfully ','success'=>true];
                          }
                     }
            }
            if($attributes['associate_member'] != $member->associate_member){
                    if($attributes['associate_member']=='1')
                    {
                        $flat = Flat::find($flat_id,['id','flat_no']);
                        if (!$flat)
                        {
                            return ['msg'=>'Flat doesnot exist with id '.$flat_id,'success'=>false];
                        }
                        $validator = \Validator::make(
                        $attributes,
                            array(
                                'first_name' => 'required',
                                'email'=>'between:3,64|email',
                                'contact_number'=>'numeric', 'digits_between:10,10',
                            )
                        );

                        if ($validator->fails()){
                            return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];
                        }

                        $user_society = UserSociety::where('flat_id',$flat_id)->where('relation','=','associate')->first();
                        if($user_society)
                        {
                            $user_email = User::where('email','=',$attributes['email'])->where('id','<>',$user_society->user_id)->first();
                            if($user_email){
                                return ['msg'=>'Email id is already taken','already_exist'=>'already taken','success'=>false];
                            }else{
                                User::where('id','=',$user_society->user_id)->delete();
                                UserSociety::where('flat_id',$flat_id)->where('relation','=','associate')->delete();
                                AclUserRole::where('user_id','=',$user_society->user_id)->delete();
                                $member_check = Member::where('flat_id','=',$flat_id)->where('associate_member','=',1)->where('id','<>',$id)->first();
                                $member_check->associate_member = 0;
                                $member_check->save();
                            }
                        }
                            if (User::whereEmail($attributes['email'])->count() > 1){
                                return ['msg'=>'Email id is already taken.','already_exist'=>'already taken','success'=>false];
                            }

                            $user = User::firstOrNew(['email' => $attributes['email']]);
                            $user->fill($attributes);
                            $password = str_random(8);
                            $user->password = bcrypt($password);
                            $user->save();

                            $userFlat = UserSociety::where('flat_id',$flat_id)->first(['id','block_id','building_id']);
                            $userSociety = new UserSociety();
                            $userSociety->user_id = $user->id;
                            $userSociety->society_id = $society_id;
                            $userSociety->flat_id = $flat_id;
                            $userSociety->block_id = $userFlat->block_id;
                            $userSociety->building_id = $userFlat->building_id;
                            $userSociety->relation = 'associate';
                            $userSociety->status = 1;
                            $userSociety->save();

                            $associateRole = AclRole::where('role_name','Associate Member')->where('society_id',$society_id)->whereNull('parent_id')->first(['id']);
                            $aclUserRole = new AclUserRole();
                            $aclUserRole->user_id = $user->id;
                            $aclUserRole->acl_role_id = $associateRole->id;
                            $aclUserRole->save();

                            $member->fill($attributes);
                            $member->associate_member = 1;
                            $member->save();

                            $block = $userSociety->block()->first(['block']);

                            if($block){
                               $block_name =  $block->block.'-';
                            }else{
                               $block_name = '';
                            }

                            $building = Society::find($userSociety->building_id,['name']);

                            $flat = $building->name.'-'.$block_name.$flat->flat_no;

                            $data = array(
                                             'name'=>$user->first_name,
                                             'email'=>$user->email,
                                             'society_name'=>$society_name,
                                             'flat_no'=>$flat,
                                             'password'=>(!empty($password)) ? $password : 'Your account is already exist.Please use your previous password for login.'
                                    );
                            if ($request->get('checkbox-associate_member')) {
                                event(new \ApartmentApi\Events\AssociateMemberWasAdded($data));
                            }
                            return ['msg'=>' updated successfully with associate member','success'=>true];
                    }
                    if($attributes['associate_member']=='0')
                    {
                        $user_society = UserSociety::where('flat_id',$flat_id)->where('relation','=','associate')->first();
                        if($user_society)
                        {
                            User::where('id','=',$user_society->user_id)->delete();
                            UserSociety::where('flat_id',$flat_id)->where('relation','=','associate')->delete();
                            AclUserRole::where('user_id','=',$user_society->user_id)->delete();
                        }
                            $member->fill($attributes);
                            $member->save();
                        return ['msg'=>' updated successfully','success'=>true] ;
                    }
                }
            }
    }
