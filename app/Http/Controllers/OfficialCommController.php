<?php

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\ApartmentUser;
use ApartmentApi\Models\officialcomm;
use Illuminate\Database\Eloquent\Model;

class OfficialCommController extends Controller {
	public function __construct() {
		$this->middleware ( 'rest' );
	}


	public function save() {
		$data = \Input::all();
		$user = OauthToken::find(\Input::get('access_token'))->user()->first();

		$reply = new OfficialComm($data);
		$reply->user()->associate($user);
		$reply->fill ($data);
		$reply->save();
		$reply->first_name = $reply->user->first_name;

		//unset($reply->user);
		return ['msg'=>'reply created successfully','data'=>$reply];

	}

	public function getCommList($id = null) {




		$offset = \Input::get ( 'page' )>1?\Input::get ( 'page' )*10-10:0;
		$where = '';
		$bindings = array();
		if (\Input::get('search',null)) {
			$where = ' and official_communication.subject like :subject  ';
			$bindings['subject'] = '%'.\Input::get('search').'%';
		}

		$results = \DB::select ( 'select official_communication.id, official_communication.title, entity.text, count(reply.id) as reply_count, users.first_name, entity.created_at, entity.updated_at
				from entity inner join users on entity.user_id = users.id left join official_communication as reply on reply.parent_id = entity.id where entity.entity_type_id = ' . Entity::ENTITY_TYPE_FORUM_TOPIC .' AND entity.title LIKE :title group by entity.id order by entity.id DESC limit :limit offset :offset
			',array('title'=>'%'.\Input::get('search').'%', 'limit'=>10, 'offset'=>$offset));

		return $results;

	}

/* 	public function getLetterCount(){
		return ['msg'=>'reply created successfully','data'=>$reply];

	}  */

    public function getLetterListAdmin() {
    	$offset = 1;
    	$where = '';
    	$bindings = array();

    	$results = \DB::select ( 'select official_communication.id, official_communication.recepient_id, official_communication.created_by, official_communication.subject, official_communication.text, users.first_name, official_communication.created_at, official_communication.updated_at, official_communication.status,official_communication.is_read
				from official_communication inner join users on entity.created_by = users.id group by official_communication.id order by official_communication.id DESC limit :limit offset :offset
				',array('limit'=>10, 'offset'=>$offset));

    	return $results;
    }

	public function getLetterCountAdmin(){


		$results = \DB::selectOne ( 'select count(official_communication.id) as letter_count from official_communication');
		return $results;
	}

}
