<?php

namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Requests\SocietyIdRequest;
use ApartmentApi\Models\Amenities;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\OfficialCommReply;
use ApartmentApi\Commands\Amenities\ListAmenitiesCommand;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Foundation\Validation\ValidatesRequests;


class AmenitiesController extends ApiController
{
	public function getAmenitiesList()
    {
		$command = new ListAmenitiesCommand;
		$amenities = $this->dispatch($command);

		return $amenities ?
            $this->presentor()->make200Response($command->getMessage(), $amenities):
            $this->presentor()->makeResponseByCode($command->getError(), $command->getStatusCode());
	}
	
	

	public function save(Request $request) {
		$amenities = new Amenities ();
		$parent = OauthToken::find ( $request->get ( 'access_token' ) )->society ()->first ()->id;
		$createdBy = OauthToken::find ( $request->get ( 'access_token' ) )->user ()->first ()->id;
		$amenities->society_id = $parent;
		$amenities->user_id = $createdBy;
		$amenities->name = $request->get ( 'name' );
		$amenities->description = $request->get ( 'description' );
		$data=$request->all ();

		$folder = 'uploads/amenities/';
		$path = public_path(); // root  folder
		$destinationPath = $path.'/'.$folder;
		if(\Request::file('file')){
			$file_name = \Request::file('file')->getClientOriginalName();

			$filenameitems = explode(".", $file_name);
			$ext=$filenameitems[count($filenameitems) - 1];
			$uploaded_name = $filenameitems[0];
			$new_file_name = str_replace(" ", "_",$uploaded_name); // Replaced white spaces with _
			$new_file_name = $new_file_name.'_'.date('d-m-y').'_'.rand(1, 20).'.'.$ext;
			\Request::file('file')->move($destinationPath,$new_file_name);
			$data['image'] = $new_file_name;
		}

		$amenities->fill ( $data );
		$amenities->save ();
		return $this->presentor->make200Response ( 'Amenities saved successfully.', $amenities );
	}

	public function getLetterList(Request $request) {

		// $offset = 0;
		$offset = \Input::get ( 'page' ) > 1 ? \Input::get ( 'page' ) * 10 - 10 : 0;
		$where = '';
		$bindings = array ();

		$society_id = OauthToken::find ( $request->get ( 'access_token' ) )->society_id;
		$user_id = OauthToken::find ( $request->get ( 'access_token' ) )->user_id;

		$results = \DB::select ( 'select official_communication.id, official_communication.recepient_id, official_communication.created_by, official_communication.subject, official_communication.text, users.first_name, official_communication.created_at, official_communication.updated_at, official_communication.status,official_communication.is_read
				from official_communication inner join users on official_communication.created_by = users.id where official_communication.recepient_id IN(select acl_role_id from acl_user_role where user_id=:user_id) group by official_communication.id order by official_communication.id DESC limit :limit offset :offset
				', array (
				'user_id' => $user_id,
				'limit' => 10,
				'offset' => $offset
		) );
		return $this->presentor->make200Response ( 'fetched succesfully', $results );
	}
	public function getList(Request $request) {

		$offset = \Input::get ( 'offset' )>1?\Input::get ( 'offset' )*5-5:0;
		$where = '';
		$bindings = array ();

		$society_id = OauthToken::find ( $request->get ( 'access_token' ) )->society_id;
		$user_id = OauthToken::find ( $request->get ( 'access_token' ) )->user_id;

		$results = \DB::select ( 'select amenities.id, amenities.user_id ,amenities.name, amenities.description, amenities.image, amenities.charges, users.first_name, date_format(amenities.created_at,"%d-%m-%Y") as created_at, amenities.updated_at
				from amenities inner join users on amenities.user_id = users.id where amenities.user_id = :user_id and amenities.society_id = :society_id group by amenities.id order by amenities.id DESC limit :limit offset :offset
				', array (
				'user_id' => $user_id,
				'society_id' => $society_id,
				'limit' =>5,
				'offset' => $offset
		) );

		return $this->presentor()->makeCustomResponse ([
                'message' => 'fetched succesfully',
                'status'  => 'success',
                'code'    => 200,
                'results' => $results,
                'total'   => $this->getAmenitiesCount($request)['amenities_count']
            ]);
	}
	public function getLetterCountAdmin(Request $request) {
		$society_id = OauthToken::find ( $request->get ( 'access_token' ) )->society_id;
		$user_id = OauthToken::find ( $request->get ( 'access_token' ) )->user_id;

		$results = \DB::select( '
				select official_communication.id, official_communication.recepient_id, official_communication.created_by, official_communication.subject, official_communication.text, users.first_name, official_communication.created_at, official_communication.updated_at, official_communication.status,official_communication.is_read
				from official_communication inner join users on official_communication.created_by = users.id where official_communication.recepient_id IN(select acl_role_id from acl_user_role where user_id=:user_id) group by official_communication.id ', array (
				'user_id' => $user_id
		) );

		return count($results);
	}

	public function getAmenitiesCount(Request $request) {
		$society_id = OauthToken::find ( $request->get ( 'access_token' ) )->society_id;
		$user_id = OauthToken::find ( $request->get ( 'access_token' ) )->user_id;

		$results = \DB::selectone ( '
				select count(amenities.id) as amenities_count from amenities
				where amenities.user_id = :user_id
				and amenities.society_id = :society_id
				order by amenities.id DESC
				', array (
							'user_id' => $user_id,
							'society_id' => $society_id,
					) );

		return ( array ) $results;
	}

	public function getDetails($id) {
		$amenities = Amenities::find ( $id );

		$results = \DB::selectOne ( "select amenities.id, amenities.name, amenities.description, amenities.image, amenities.charges
				from amenities inner join users on amenities.user_id = users.id where amenities.id =:id", [
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

    public function delete()
    {
       $id = \Input::get('id');
        Amenities::where('id','=',$id)->delete();

		return ['msg'=>'Amenties with id '.$id.' deleted successfully.','success'=>true];
    }

    public function updateAmenities(Request $request,$id)
    {
        $data = $request->all();
//         print_r($data);exit;
        $amenities = Amenities::where('id','=',$id)->firstOrFail();
        if(!$amenities)
        {
            return ['error'=>'Amenities not found with id: '.$id,'success'=>false];
        }else{
            $folder = 'uploads/amenities/';
            $path = public_path(); // root  folder
            $destinationPath = $path.'/'.$folder;
            if(\Request::file('image')){
                $file_name = \Request::file('image')->getClientOriginalName();
                $filenameitems = explode(".", $file_name);
                $ext=$filenameitems[count($filenameitems) - 1];
                $uploaded_name = $filenameitems[0];
                $new_file_name = str_replace(" ", "_",$uploaded_name); // Replaced white spaces with _
                $new_file_name = $new_file_name.'_'.date('d-m-y').'_'.rand(1, 20).'.'.$ext;
                \Request::file('image')->move($destinationPath,$new_file_name);
                $data['image'] = $new_file_name;

            }
                $amenities->fill($data);
                $amenities->save ();
                return $this->presentor->make200Response ( 'Amenities saved successfully.', $amenities );
        }
    }
}
