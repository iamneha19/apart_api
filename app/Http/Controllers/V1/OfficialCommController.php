<?php

namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Http\Middleware\Rest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use ApartmentApi\Models\OauthToken;
use \ApartmentApi\Models\OfficialComm;
use \ApartmentApi\Models\OfficialCommReply;
use ApartmentApi\Models\User;

class officialCommController extends ApiController {
	public function __construct() {
		parent::__construct ();
	}
	public function save(Request $request) {
		$officialComm = new OfficialComm ();
		$parent = OauthToken::find ( $request->get ( 'access_token' ) )->society ()->first ()->id;
		$createdBy = OauthToken::find ( $request->get ( 'access_token' ) )->user ()->first ()->id;
		$officialComm->society_id = $parent;
		$officialComm->created_by = $createdBy;
		$officialComm->recepient_id = $request->get ( 'recepient_id' );
		$officialComm->status = 'pending';
		$officialComm->fill ( $request->all () );
		$officialComm->save ();
		
		return $this->presentor->make200Response ( 'communication saved successfully.', $officialComm );
	}
	public function update() {
		$data = \Input::all ();
		$id = \Input::get ( 'id' );
		$status = \Input::get ( 'status' );
		// $officialComm = new OfficialComm();
		$officialComm = OfficialComm::find ( $id );
		$officialComm->status = $status;
		$officialComm->save ();
	}
	public function getLetterList(Request $request) {
		$society_id = OauthToken::find ( $request->get ( 'access_token' ) )->society_id;
		$user_id = OauthToken::find ( $request->get ( 'access_token' ) )->user_id;
		// $offset = 0;
		$offset = \Input::get ( 'page' )>1?\Input::get ( 'page' )*5-5:0;
                $search = $request->get('search',null);
                
                if (\Input::get('offset',0))
                    $offset = \Input::get('offset',0);
                if (\Input::get('limit',20))
                    $limit = \Input::get('limit',20);
                else
                    $limit = 10;
		$where = ' and official_communication.society_id= '.$society_id ;
        $whereSep = '';
		
        $bindings = array ();
        if ($request->get('search',null)) {
            $where = ' and official_communication.society_id= '.$society_id. ' and official_communication.subject like "%'.$request->get('search').'%"';
            $whereSep = true;
           
        }
//        print_r($bindings);exit;
		
                
                $role = \DB::selectOne('SELECT role_name FROM acl_user_role 
                    INNER JOIN acl_role ON acl_role.id = acl_user_role.acl_role_id
                    WHERE user_id ='. $user_id);
                
                $sort = "order by official_communication.id DESC";
                if (\Input::get('sort',null)){
                    $sort =  \Input::get('sort',null);
                     if($sort == 'subject'){
                         $sort = ' order by official_communication.subject '.\Input::get('sort_order','desc').' ';
                     }else{
                        $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','desc').' ';
                     }

                 }
                 $roleName = $role->role_name;
                
               if($roleName == "Admin") {
                   
                        $results = \DB::select ('select official_communication.id, official_communication.recepient_id, official_communication.created_by, official_communication.subject, official_communication.text, users.first_name, date_format(official_communication.created_at,"%d-%m-%Y") as created_at, official_communication.updated_at, official_communication.status,official_communication.is_read
				from official_communication inner join users on official_communication.created_by = users.id'.$where.' group by official_communication.id '.$sort.' limit :limit offset :offset', 
                                array_merge($bindings,array (				
				'limit' => $limit,
				'offset' => $offset 
                                )) );                	
                        $count =\DB::selectone('select count(official_communication.id) as letter_count
				from official_communication inner join users on official_communication.created_by = users.id'.$where                          
                            );
               } else {
                        $results = \DB::select ('select official_communication.id, official_communication.recepient_id, official_communication.created_by, official_communication.subject, official_communication.text, users.first_name, date_format(official_communication.created_at,"%d-%m-%Y") as created_at, official_communication.updated_at, official_communication.status,official_communication.is_read
                                    from official_communication inner join users on official_communication.created_by = users.id where official_communication.recepient_id IN(select acl_role_id from acl_user_role where user_id=:user_id) '.$where.' group by official_communication.id '.$sort.' limit :limit offset :offset', 
                                    array_merge($bindings,array (
                                    'user_id' => $user_id,
                                    'limit' => $limit,
                                    'offset' => $offset 
                                )) );
                
			  	
			$count =\DB::selectone('select count(official_communication.id) as letter_count
				from official_communication inner join users on official_communication.created_by = users.id where official_communication.recepient_id IN(select acl_role_id from acl_user_role where user_id=:user_id) '.$where, 
                                array_merge($bindings,array (
                                'user_id' => $user_id,
                                )) );
                    }   
                              
		return ['total'=>$count,'msg'=>'data fetched successfully','results'=>$results];
		
		
	}
	public function getLetterListResident(Request $request) {
		
		$offset = \Input::get ( 'page' )>1?\Input::get ( 'page' )*5-5:0;
                $search = $request->get('search',null);
		$where = '';
		$bindings = array ();
       
        if ($request->get('search',null)) {
            $where = ' and official_communication.subject like  "%'.$request->get('search').'%"';
            $whereSep = true;            
        }
//		print_r($where);exit;
		$society_id = OauthToken::find ( $request->get ( 'access_token' ) )->society_id;
		$user_id = OauthToken::find ( $request->get ( 'access_token' ) )->user_id;
//		dd($society_id);
		$results = \DB::select ( 'select official_communication.id, official_communication.recepient_id, official_communication.created_by, official_communication.subject, official_communication.text, users.first_name, date_format(official_communication.created_at,"%d-%m-%Y") as created_at , date_format(official_communication.updated_at,"%d-%m-%Y") as updated_at, official_communication.status,official_communication.is_read
				from official_communication inner join users on official_communication.created_by = users.id where official_communication.created_by = :user_id and official_communication.society_id = :society_id  '.$where.' group by official_communication.id order by official_communication.id DESC limit :limit offset :offset
				',array_merge($bindings, array (
				'user_id' => $user_id,
				'society_id' => $society_id,				
                                    'limit'=>\Input::get('limit',10),
                                    'offset'=>\Input::get('offset',0),
                                    )                
                                ));
               
                
               $count = \DB::selectone ( '
                        select count(official_communication.id) as letter_count from official_communication 
                        where official_communication.created_by = ' .$user_id. 
                        ' and official_communication.society_id = ' .$society_id.
                        $where	);		
                       

                return ['count'=>$count,'msg'=>'data fetched sucessfully','results'=>$results];

	}
       
	public function getLetterCountAdmin(Request $request) {
		$society_id = OauthToken::find ( $request->get ( 'access_token' ) )->society_id;
		$user_id = OauthToken::find ( $request->get ( 'access_token' ) )->user_id;
		if($request->has('search')) {
                     $count = \DB::selectOne('select count( official_communication.id ) AS count from official_communication
                            where created_by = '.$user_id.
                            ' and society_id = '.$society_id.
                            ' and  official_communication.subject like "%'.$request->get('search').'%"');
                } else {
		$results = \DB::select( '
				select official_communication.id, official_communication.recepient_id, official_communication.created_by, official_communication.subject, official_communication.text, users.first_name, official_communication.created_at, official_communication.updated_at, official_communication.status,official_communication.is_read
				from official_communication inner join users on official_communication.created_by = users.id where official_communication.recepient_id IN(select acl_role_id from acl_user_role where user_id=:user_id) group by official_communication.id ', array (
				'user_id' => $user_id
		) ); 
                }
		
		return count($results);
	}
	
	public function getLetterCountResident(Request $request) {
		$society_id = OauthToken::find ( $request->get ( 'access_token' ) )->society_id;
		$user_id = OauthToken::find ( $request->get ( 'access_token' ) )->user_id;
	
		$results = \DB::selectone ( '
				select count(official_communication.id) as letter_count from official_communication 
				where official_communication.created_by = :user_id 
				and official_communication.society_id = :society_id 				
				', array (
							'user_id' => $user_id,
							'society_id' => $society_id,
					) );
	
		return ( array ) $results;
	}
	
	public function getLetter($id) {
		$officialComm = OfficialComm::find ( $id );
		$officialComm->is_read = 1;
		$officialComm->save ();
		
		$results = \DB::selectOne ( "select official_communication.id, official_communication.created_by, official_communication.subject, official_communication.text, users.first_name, official_communication.created_at
				from official_communication inner join users on official_communication.created_by = users.id where official_communication.id =:id", [ 
				'id' => $id 
		] );
		
		return ( array ) $results;
	}
	public function getLetterTo($id) {
		$results = \DB::selectOne ( "select official_communication.id, acl_role.role_name 
				from official_communication inner join acl_role on official_communication.recepient_id = acl_role.id where official_communication.id =:id ", [ 
				'id' => $id 
		] );
		
		return ( array ) $results;
	}
	public function saveReply(Request $request) {
		$officialCommReply = new OfficialCommReply ();
		$letter_id = \Input::get ( 'letter_id' );
		$comment = \Input::get ( 'comment' );
		$society_id = OauthToken::find ( $request->get ( 'access_token' ) )->society ()->first ()->id;
		$user_id = OauthToken::find ( $request->get ( 'access_token' ) )->user ()->first ()->id;
		$officialCommReply->society_id = $society_id;
		$officialCommReply->user_id = $user_id;
		$officialCommReply->letter_id = $letter_id;
		$officialCommReply->comment = $comment;
		$officialCommReply->save ();
		
		return $this->presentor->make200Response ( 'communication reply saved successfully.', $officialCommReply );
	}
	public function getOfficialCommReplyList($id) {
		$offset = 0;
		// $society_id = OauthToken::find ( $request->get ( 'access_token' ) )->society ()->first ()->id;
		// $user_id = OauthToken::find ( $request->get ( 'access_token' ) )->user ()->first ()->id;
		
		$results = \DB::select ( 'select official_communication_reply.id, official_communication_reply.comment, users.first_name
				from official_communication_reply inner join users on official_communication_reply.user_id = users.id where official_communication_reply.letter_id=:id limit :limit offset :offset ', array (
				'id' => $id,
				'limit' => 10,
				'offset' => $offset 
		) );
		
		return $results;
	}
        
//        public function search(Request $request) {
//                
//            $offset = \Input::get ( 'page' )>1?\Input::get ( 'page' )*5-5:0;
//                $search = $request->get('search',null);
//		$where = 'group by official_communication.id order by official_communication.id DESC limit :limit offset :offset';
//		$bindings = array ();
//       
//                if ($request->get('search',null)) {
//                    $where .= 'and official_communication.subject like :subject';
//                    $whereSep = true;
//                    $bindings['subject'] = '%'.$request->get('search').'%';
//                }
//                
//		$society_id = OauthToken::find ( $request->get ( 'access_token' ) )->society_id;
//		$user_id = OauthToken::find ( $request->get ( 'access_token' ) )->user_id;  
//                
//            $results = \DB::select ( 'select official_communication.id, official_communication.recepient_id, official_communication.created_by, official_communication.subject, official_communication.text, users.first_name, date_format(official_communication.created_at,"%d-%m-%Y") as created_at , date_format(official_communication.updated_at,"%d-%m-%Y") as updated_at, official_communication.status,official_communication.is_read
//                        from official_communication inner join users on official_communication.created_by = users.id where official_communication.created_by = :user_id and official_communication.society_id = :society_id '.$where. 
//                        ,array_merge($bindings, array (
//                        'u    ser_id' => $user_id,
//                        'society_id' => $society_id,
//                        'limit' => 5,
//                        'offset' => $offset 
//                
//                        ) 
//                ));   
//           $count = count($results);
//             return ['count'=>$count,'msg'=>'data fetched sucessfully','results'=>$results];
//        }   
}
