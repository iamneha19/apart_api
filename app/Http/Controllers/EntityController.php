<?php
/**
 * @author Sumeet Badiger
 */
namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\Entity;
use ApartmentApi\Models\EntityLike;
use Illuminate\Support\Facades\Input;
use ApartmentApi\Models\ApartmentApi\Models;
use ApartmentApi\Models\OauthToken;
use Illuminate\Http\Request;
use ApartmentApi\Models\UserGroup;

Class EntityController extends Controller {
	
	protected $entity;
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->entity = new Entity();
		$this->middleware('rest');
	}
	
	public function postList(Request $request,$parent_id = null) {
		
		$offset = \Input::get('offset',0);
		
		$binds = ['offset'=>$offset,'access_token'=>$request->get('access_token'),'token'=>$request->get('access_token')];
		$where = '';
		
		if ($parent_id) {
			$where .= ' and e.parent_id = :parent_id ';
			$binds['parent_id'] = $parent_id;
			
		} else {
			$where .= ' and e.parent_id is null ';
		}
		
		$ids = implode(',', [Entity::ENTITY_TYPE_POST,Entity::ENTITY_TYPE_POLL,Entity::ENTITY_TYPE_IMAGE]);
		
		$sql = <<<EOF
		select e.id,u.first_name,e.title,e.`text`,e.created_at,e.entity_type_id,
		rply.reply_count,ifnull(lk.like_count,0) as like_count,user_lk.liked as liked
		from entity e left join users u on u.id = e.user_id 
		inner join oauth_token on oauth_token.society_id = e.society_id 
		left join 
			(select id,parent_id,count(parent_id) reply_count from entity group by parent_id) as rply on rply.parent_id = e.id
		left join
			(select id,entity_id,count(entity_id) like_count from entity_like group by entity_id) as lk on lk.entity_id = e.id 
		left join (
		select count(entity_id) liked,entity_id from entity_like inner join oauth_token
		 on oauth_token.user_id = entity_like.user_id where oauth_token.token = :access_token group by entity_id
		 ) as user_lk on user_lk.entity_id = e.id 
		where e.entity_type_id in ($ids) and oauth_token.token = :token $where 
		 and e.deleted_at is null order by e.created_at desc limit 50 offset :offset;
EOF;
		$results = \DB::select($sql,$binds);
		
		foreach ($results as $k=>$v) {
			$results[$k]->replies = [];
		}
		return $results;
		
	}
	
	public function replyList(Request $request, $postId) {
		
		$binds = array(
			'entity_id'=>$postId,
			'offset'=>$request->get('offset',0),
			'access_token'=>$request->get('access_token')
		);
		
		$results = \DB::select('
			select e.id,u.first_name,e.`text`,e.created_at,ifnull(lk.like_count,0) like_count,user_lk.liked as liked
			from entity e 
			left join users u on u.id = e.user_id 
			left join 
				(select entity_id,count(*) like_count from entity_like group by entity_id) as lk on lk.entity_id = e.id
			left join (
			select count(entity_id) liked,entity_id from entity_like inner join oauth_token
			 on oauth_token.user_id = entity_like.user_id where oauth_token.token = :access_token group by entity_id
			 ) as user_lk on user_lk.entity_id = e.id 
			
			where e.parent_id = :entity_id and e.entity_type_id = 5 and e.deleted_at is null limit 15 offset :offset;
		',$binds);
		
		return $results;
	}
	
	public function item($id) {
		
		$post = $this->entity->find($id);
		
		if (!$post)
			return ['msg'=>'post doesnt exist with id - '.$id];
		
		return $post;
	}
	
	public function storeOrUpdatePost(Request $request) {
		// get all posted form data
                $oauthToken = OauthToken::find($request->get('access_token'));
                $society = $oauthToken->society()->first();
		$attributes = \Input::all();
		$id = \Input::get('id');
		$attributes['entity_type_id'] = Entity::ENTITY_TYPE_POST;
		$user = OauthToken::find(\Input::get('access_token'))->user()->first();
		//echo $user;
		// sample validation
		$validator = \Validator::make(
				$attributes,
				array('title' => 'required|min:5','text'=>'required|min:5','entity_type_id'=>'required')
		);
		
		if ($validator->fails())
			return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
		
		if ($id) {
			$entity = $this->entity->where('id','=',$id)->where('entity_type_id','=',Entity::ENTITY_TYPE_POST);
			if (!$entity)
				return ['msg'=>'post not found'];
		} else {
			$entity = new Entity();
		}
		
		$entity->user()->associate($user);
        $entity->society()->associate($society);
		
		$entity->fill($attributes);
		$entity->save();
		$entity->first_name = $entity->user->first_name;
		$entity->replies = [];
		unset($entity->user);
		$entity->liked = false;
		$entity->like_count = 0;
		$entity->reply_count = 0;
		//$this->entity->updateOrCreate(['id'=>$id],$attributes);
		
		return ['msg'=>$id ? 'post updated successfully':'post created successfully','data'=>$entity];
	}
	
	
	public function storeOrUpdateForumTopic() {
		// get all posted form data
               
		$attributes = \Input::all();
               $attributes['text'] =  $attributes['editor_start'];
//               dd($attributes);
		$id = \Input::get('id');
		$attributes['entity_type_id'] = Entity::ENTITY_TYPE_FORUM_TOPIC;
		$user = OauthToken::find(\Input::get('access_token'))->user()->first();
                $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
		// sample validation
                
		$validator = \Validator::make(
				$attributes,
				array('title' => 'required|min:5','text'=>'required|min:5','entity_type_id'=>'required')
		);
	
		if ($validator->fails())
			return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
                
                
		if ($id) {
			
			$entity = Entity::where('id','=',$id)->where('entity_type_id','=',Entity::ENTITY_TYPE_FORUM_TOPIC);
			if (!$entity)
				return ['msg'=>'forum topics not found'];
		} else {
			$entity = new Entity($attributes);
		}
		//$entity->fill($attributes);
		$entity->user()->associate($user);
        $entity->society_id = $society_id;
		$entity->save();
		//Entity::updateOrCreate(['id'=>$id],$attributes);
                
                if (\Request::hasFile('file'))
                {
                    $folder_path = 'uploads/'.$society_id.'/files/forum/'.$entity->id.'/';
                    $folder_type = 'forum';
                    $file_data = array('folder_id'=>$entity->id);
                   
                    $file =  \App::make('services')->uploadFile($file_data,$folder_type,$folder_path,$id);
                   
                }
	
		return ['msg'=>$id ? 'Forum topic updated successfully':'forum topic created successfully','data'=>$entity];
	}
	
	
	public function storeReply() {
		
		$data = \Input::all();
		$data['entity_type_id'] = Entity::ENTITY_TYPE_REPLY;
		$user = OauthToken::find(\Input::get('access_token'))->user()->first();
		
		// sample validation
		$validator = \Validator::make(
				$data,
				array('text'=>'required|min:5','entity_type_id'=>'required')
		);
		
		if ($validator->fails())
			return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
		
		$forumid = \Input::get('post_id');
		
		$post = $this->entity->where('id','=',$forumid)
				->where('entity_type_id','=',Entity::ENTITY_TYPE_POST)
				->firstOrFail();
		
		if (!$post)
			return ['error'=>'Post not found with id: '.$forumid];
		
		$reply = new Entity($data);
		$reply->user()->associate($user);
		$post->children()->save($reply);
		$reply->first_name = $reply->user->first_name;
		$reply->like_count = 0;
		unset($reply->user);
		return ['msg'=>'reply created successfully','data'=>$reply];
	}
	

	public function storeReplyForumTopic() {
		
	
		$data = \Input::all();
		$data['entity_type_id'] = Entity::ENTITY_TYPE_REPLY;
		$user = OauthToken::find(\Input::get('access_token'))->user()->first();
		// sample validation
		$validator = \Validator::make(
				$data,
				array('title' => 'required|min:1','text'=>'required|min:1','entity_type_id'=>'required')
		);
	
		if ($validator->fails())
			return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
	
		$forumid = \Input::get('parent_id');
	
		$post = $this->entity->where('id','=',$forumid)
		->where('entity_type_id','=',10)
		->firstOrFail();
	
		if (!$post)
			return ['error'=>'forum topic not found with id: '.$forumid];
	
		$reply = new Entity($data);
		$reply->user()->associate($user);
		$post->children()->save($reply);
		$reply->first_name = $reply->user->first_name;
		unset($reply->user);
		return ['msg'=>'reply created successfully','data'=>$reply];
	}
	
	public function storeReplyOfficialCommunication(Request $request) {
	
 		$data = \Input::all();
		$data['entity_type_id'] = Entity::ENTITY_TYPE_OFFICIAL_COMM_REPLY;
		$user = OauthToken::find(\Input::get('access_token'))->user()->first();
		// sample validation
/* 		$validator = \Validator::make(
				$data,
				array('text'=>'required|min:1','entity_type_id'=>'required')
		);
	
		if ($validator->fails())
			return ['msg'=>'Input errors','input_errors'=>$validator->messages()]; */
	
 		$replyid = \Input::get('parent_id');
//  		print_r($replyid);exit;
// 		$post = Entity::where('parent_id','=',$replyid)
// 		->where('entity_type_id','=',12)
// 		->first();
// // 			print_r($post);exit;
// 		if (!$post)
// 		{
// 			return ['error'=>'comment not found with id: '.$replyid];
// 		}else{
		$reply = new Entity();
		$reply->fill($data);
		$reply->parent_id = "683";
		$reply->user()->associate($user);
// 		$reply->society_id = "327";
		$reply->save();
		/* $post->children()->save($reply);
		$reply->first_name = $reply->user->first_name; */
// 		unset($reply->user);
		return ['msg'=>'comment created successfully','data'=>$reply]; 
		
// 		}
		
/* 		$entity = new Entity();
		
		$parent = OauthToken::find($request->get('access_token'))->society()->first()->id;
		$createdBy = OauthToken::find($request->get('access_token'))->user()->first()->id;
		$entity->society_id = $parent;
		$entity->user_id = $createdBy;
		$entity->fill($request->all());
		$entity->save();
		return $this->presentor->make200Response('reply saved successfully.', $entity); */
		
	}
	

	public function storeLike(Request $request) {
		
		$entity = $this->entity->findOrFail(\Input::get('entity_id'));
		
		$user = OauthToken::find(\Input::get('access_token'))->user()->first();
		
		if (!$entity) {
			return ['msg'=>'post not found with id: '.\Input::get('entity_id')];
		}
		
		if ($request->get('like') == 'true') {
			$like = new EntityLike();
			$like->entity()->associate($entity);
			$like->user()->associate($user);
			
			$like->save();
		} else {
			EntityLike::where('entity_id','=',$entity->id)
								->where('user_id','=',$user->id)->first()->delete();
		}
		
		
		return ['msg'=>'like saved'];
	}
		
	public function delete($id) {
		
		$post = $this->entity->find($id);
		$post->delete();
		
		return ['msg'=>'entity deleted successfully'];
	}
	
	/**
	 * restore deleted entity
	 */
	public function restore() {
		
		$post = $this->entity->where('id','=',$id)->where('entity_type_id','=',Entity::ENTITY_TYPE_POST);
		$post->restore();
		
		return ['msg'=>'post restored successfully'];
	}
	
	public function groupList(Request $request) {
             $societyId = OauthToken::find(\Input::get('access_token'))->society()->first()->id;
            $where = ' and entity.deleted_at is null';
            $offset = \Input::get('offset',0);
            $limit = \Input::get('limit',10);
           
            $bindings = ['token'=>$request->get('access_token'),'token1'=>$request->get('access_token')];
		
		if ($request->has('search')) {
			$where .= ' and entity.title like "%'.$request->get('search').'%"';
			
                }
		
            $sql = <<<EOF
                select entity.id,entity.society_id,entity.title,entity.text,user_group.user_id,users.first_name,users.last_name from entity
                    inner join users on entity.user_id = users.id
                    INNER JOIN oauth_token on oauth_token.society_id = entity.society_id 
                        left join (
                            select oauth_token.user_id,user_group.group_id,oauth_token.token from oauth_token 
                            inner join user_group on user_group.user_id = oauth_token.user_id 
                            where oauth_token.token = :token)
                as user_group on user_group.group_id = entity.id
                where oauth_token.token = :token1 and entity.entity_type_id = 3 $where
                and entity.society_id = $societyId
                order by user_group.user_id desc limit $limit offset $offset
                   
                    
EOF;
             
            
               $count = count( \DB::select("select entity.id from entity
                where society_id = $societyId
                and entity.entity_type_id = '3' $where
                "));
                
                     return['count'=>$count,'data'=> \DB::select($sql,$bindings)];
                  
                           
                }
	public function storeOrUpdateGroup(Request $request) {                
                $oauthToken = OauthToken::find($request->get('access_token'));
                $society = $oauthToken->society()->first();
		$attributes = \Input::all();               
		$id = \Input::get('id');                
		$attributes['entity_type_id'] = Entity::ENTITY_TYPE_GROUP;                
		$validator = \Validator::make(
				$attributes,
				array('title' => 'required|min:5','text'=>'required|min:5','entity_type_id'=>'required')
		);
		
		if ($validator->fails())
			return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
		
		if ($id) {
                        $entity = Entity::find($id);
//                                  ->where('entity_type_id','=',Entity::ENTITY_TYPE_GROUP);
                            $entity->fill($attributes);                             
                            $entity->save();
                            $oauthToken = OauthToken::find($request->get('access_token'));
                            $user = $oauthToken->user()->first();
                        
                      
			if (!$entity)
				return ['msg'=>'post not found'];
		} else {
			$entity = new Entity();
		$user = OauthToken::find($request->get('access_token'))->user()->first()->id;
                
                $entity->user_id = $user;
		$entity->fill($attributes);
                $entity->society()->associate($society);
                
		
		$entity->save();
                $groupId = $entity->id;
                $oauthToken = OauthToken::find($request->get('access_token'));
                $user = $oauthToken->user()->first();
                $group = Entity::find($groupId);
                $userGroup = new UserGroup();
                $userGroup->user()->associate($user);
                $userGroup->group()->associate($group);
                $userGroup->save();
                }
		//$this->entity->updateOrCreate(['id'=>$id],$attributes);
		
		return [
                              
				'msg'=>$id ? 'group updated successfully':'group created successfully',
				'data'=>[
					'id'=>$entity->id,
					'title'=>$entity->title,
					'text'=>$entity->text,
                                        'user_id'=>$user->id
				]
				
		];
			
	}
	
	public function getGroup($id = null) {
		
		$group = $this->entity->where('id','=',$id)
			->where('entity_type_id','=',3)
			->first();
		
		if (!$group)
			return ['msg'=>'group not found'];
		
		return $group;
	}
	
	public function addPostToGroup(Request $request) {
		
		$data = \Input::all();
		$groupid = \Input::get('group_id');
		$oauthToken = OauthToken::find($request->get('access_token'));
		$society = $oauthToken->society()->first();
		
		$user = $oauthToken->user()->first();
		
		$group = $this->entity->where('id','=',$groupid)
			->where('entity_type_id','=',Entity::ENTITY_TYPE_GROUP)
			->first();
		
		$post = new Entity($data);
		$post->entity_type_id = 1;
		$post->user()->associate($user);
		$post->society()->associate($society);
		$group->children()->save($post);
		
		$post->first_name = $post->user->first_name;
		$post->replies = [];
		$post->like_count = 0;
		$post->reply_count = 0;
		unset($post->user);
		
		return ['msg'=>'Post added to group successfully','data'=>$post];
	}
	
	public function deleteGroup() {
		
		$post = $this->entity->where('id','=',$id)->where('entity_type_id','=',Entity::ENTITY_TYPE_GROUP);
		$post->delete();
		
		return ['msg'=>'group deleted successfully'];
	}
	
	/// upload photos
/* 	public function uploadAdminForumFiles() {
		$society_id = OauthToken::find(\Input::get('access_token'))->society_id;
		// get all posted form data
		$data = \Input::all();
	
		if (\Request::hasFile('file'))
		{
			$folder_path = 'uploads/'.$society_id.'/forum/';
			$folder_type = 'forum';
			$data['visible_to']= 3;
			$file =  \App::make('services')->uploadFile($data,$folder_type,$folder_path);
			dd($file);
			if(!empty($file))
			{
				return ['msg'=>'File uploaded successfully','success'=>true];
			}else{
	
				return ['msg'=>'Error in file upload','success'=>false];
			}
		}else{
			return ['msg'=>'Error in file upload','success'=>false];
		}
	
	} */
	public function uploadAdminForumFiles()
	{
		dd(Input::file());
	
		return Response::json(
				array(
						"files" => array(
								"name" => "post"
						))
		);
	}
        
       
        public function edit($id) {
             $entity = Entity::where('id', $id)->first();
                
            return [                              
                    'msg'=>$id ? 'group updated successfully':'group created successfully',
                    'data'=>[
                            'id'=>$entity->id,
                            'title'=>$entity->title,
                            'text'=>$entity->text,
                            'user_id'=>$entity->user_id
                    ]
				
		];
        }
}
