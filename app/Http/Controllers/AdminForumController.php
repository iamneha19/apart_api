<?php

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\ApartmentUser;
use ApartmentApi\Models\Entity;
use ApartmentApi\Models\OauthToken;

class AdminForumController extends Controller {
	public function __construct() {
		$this->middleware ( 'rest' );
	}
	public function getTopicList() {
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            $limit = \Input::get('limit', 5);

            $offset = \Input::get('offset', 0);

			//$offset = \Input::get ( 'page' )>1?\Input::get ( 'page' )*10-10:0;
 			$where = '';
			$bindings = array();
			//DD($offset);
			if (\Input::get('search',null)) {
				$where = ' and entity.title like :title  ';
				$bindings['title'] = '%'.\Input::get('search').'%';
				//$bindings['first_name'] = '%'.\Input::get('search').'%';
			}

 			// getting list by using model
/* 			$results = \DB::select ( 'select entity.id, entity.title, entity.text, count(reply.id) as reply_count, users.first_name, entity.created_at, entity.updated_at
				from entity inner join users on entity.user_id = users.id left join entity as reply on reply.parent_id = entity.id where entity.entity_type_id = ' . Entity::ENTITY_TYPE_FORUM_TOPIC .' '. $where. ' group by entity.id limit :limit offset :offset', array_merge($bindings,array(
                            'limit'=>\Input::get('limit',20),
                            'offset'=>\Input::get('offset',0)
                        )
                    ));

 */

/* 			$results = \DB::select ( 'select entity.id, entity.title, entity.text, count(reply.id) as reply_count, users.first_name, entity.created_at, entity.updated_at
				from entity inner join users on entity.user_id = users.id left join entity as reply on reply.parent_id = entity.id where entity.entity_type_id = ' . Entity::ENTITY_TYPE_FORUM_TOPIC .' AND entity.title LIKE :title group by entity.id order by entity.id DESC limit :limit offset :offset
				',array('title'=>'%'.\Input::get('search').'%', 'limit'=>10, 'offset'=>$offset));

			return $results;


	}

 */

                        $sort="";
                        if (\Input::get('sort',null)){
                            $sort =  \Input::get('sort',null);
                             if($sort == 'flat'){
                                 $sort = ' order by entity.title '.\Input::get('sort_order','asc').' ';
                             }else{
                                $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','asc').' ';
                             }

                         }
			$results = \DB::select ( 'select entity.id, entity.title, entity.text, count(reply.id) as reply_count, users.first_name,date_format(entity.created_at,"%d-%m-%Y") as created_at, entity.updated_at
				from entity inner join users on entity.user_id = users.id left join entity as reply on reply.parent_id = entity.id where entity.society_id = "'.$society_id.'" AND entity.entity_type_id = ' . Entity::ENTITY_TYPE_FORUM_TOPIC .' AND entity.title LIKE :title group by entity.id '.$sort.' limit :limit offset :offset
				',array('title'=>'%'.\Input::get('search').'%', 'limit'=>$limit, 'offset'=>$offset));

            $total = \DB::select ( 'select entity.id, entity.title, entity.text, count(reply.id) as reply_count, users.first_name, entity.created_at, entity.updated_at
                    from entity inner join users on entity.user_id = users.id left join entity as reply on reply.parent_id = entity.id where entity.society_id = "'.$society_id.'" AND entity.entity_type_id = ' . Entity::ENTITY_TYPE_FORUM_TOPIC .' AND entity.title LIKE :title group by entity.id '.$sort ,array('title'=>'%'.\Input::get('search').'%'));
			return [
                'useTotal' => count($total),
                'results'  => $results,
            ];


	}

	public function getTopicCount(){
        $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
		$results = \DB::selectOne ( 'select count(entity.id) as topic_count from entity where entity.society_id = "'.$society_id.'" and entity_type_id = ' . Entity::ENTITY_TYPE_FORUM_TOPIC . '');
                return $results;
	}

}
