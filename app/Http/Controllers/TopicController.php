<?php

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\ApartmentUser;
use ApartmentApi\Models\Entity;

class TopicController extends Controller {
	public function __construct() {
		$this->middleware ( 'rest' );
	}
	
	
	
	public function getTopicDetails($id) {
		
		// getting list by using model
		$results = \DB::selectOne ( "select file.*,entity.id, entity.title, entity.text, count(reply.id) as reply_count, users.first_name
				from entity inner join users on entity.user_id = users.id left join entity as reply on reply.parent_id = entity.id left join file on entity.id = file.folder_id and file.folder_type = 'forum' where entity.id=:id AND entity.entity_type_id = " . Entity::ENTITY_TYPE_FORUM_TOPIC, [ 
				'id' => $id 
		] );
		
		return $results;
	}
	public function getReplyList($id) {
		$offset = \Input::get ( 'repliesoffset' );
		// getting list by using model
		$results = \DB::select ( 'select entity.title, entity.text, users.first_name
				from entity inner join users on entity.user_id = users.id where entity.parent_id=:id AND entity.entity_type_id = ' . Entity::ENTITY_TYPE_REPLY . '  limit :limit offset :offset ' 
				,array('id' => $id , 'limit'=>10, 'offset'=>$offset)
				);
		
		return $results;
	}
}