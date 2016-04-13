<?php namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Http\Controllers\Controller;

use Illuminate\Http\Request;
use ApartmentApi\Models\Entity;
use ApartmentApi\Models\Notice;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\NoticeAttendee;
use HTML2PDF;
use Response;

class NoticeBoardController extends Controller {


	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('rest');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$society_id = OauthToken::find(\Input::get('access_token'))->society_id;
		$search = \Input::get('search',null);	
		$type = \Input::get('type',1);
		$binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];
		
		$where = ' where e.society_id='.$society_id.' and n.expiry_date > now()';
		if ($search) {
			$where .= ' and e.title like :search ';
			$binds['search'] = '%'.$search.'%';
		}
		if ( $type != 0){
			$where .= ' and n.type = '.$type;
		}
		;
		$items = \DB::select(
				"select e.* , date_format(e.created_at,'%d-%m-%Y') as created_at,n.type from entity e inner join notice n on n.id = e.id $where order by e.id desc limit :limit offset :offset"
				,$binds
				);
		
		$count = \DB::selectOne(
					"select count(*) as total from notice n inner join entity e on e.id = n.id $where",
					$search ? array('search'=>'%'.$search.'%') : array()
					
				);
		
//		$count = \DB::selectOne(
//					"select count(*) as total from notice n inner join entity e on e.id = n.id $where",
//					$search ? array('search'=>'%'.$search.'%','type'=>$type) : array('type'=>$type)
//				);

		return ['total'=>$count->total,'data'=>$items,'success'=>true];
	}
	
	public function getresinotices()
	{
		$society_id = OauthToken::find(\Input::get('access_token'))->society_id;
        $type = \Input::get('type',1);

		$binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];

		$where = ' where e.society_id='.$society_id.' and n.expiry_date > now() and status=1';
		$search = \Input::get('search',null);	
		if ($search) {
			$where .= ' and e.title like :search ';
			$binds['search'] = '%'.$search.'%';
		}
		if ( $type != 0 ){
			$where .= " AND n.type =" . $type ;
		}		
		$items = \DB::select(
				"select e.* , date_format(e.created_at,'%d-%m-%Y') as created_at,n.type from entity e inner join notice n on n.id = e.id $where order by e.id desc limit :limit offset :offset"
				,$binds
				);

		$count = \DB::selectOne(
					"select count(*) as total from notice n inner join entity e on e.id = n.id $where",
					$search ? array('search'=>'%'.$search.'%') : array()
				);

		return ['total'=>$count->total,'data'=>$items,'success'=>true];
	}

        /**
	 * Display a listing of expired notices.
	 *
	 * @return Response
	 */
	public function getExpired()
	{
		$society_id = OauthToken::find(\Input::get('access_token'))->society_id;
        $type = \Input::get('type',1);

		$binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];

		$where = ' where e.society_id='.$society_id.' and n.expiry_date < now()';
		if ( $type != 0 ){
			$where .= ' AND n.type ='. $type;
		}
		$search = \Input::get('search',null);	
		if ($search) {
			$where .= ' and e.title like :search ';
			$binds['search'] = '%'.$search.'%';
		}

		$items = \DB::select(
				"select e.* , date_format(n.expiry_date,'%d-%m-%Y') as expiry_date,n.type  from entity e inner join notice n on n.id = e.id $where order by e.id desc limit :limit offset :offset"
				,$binds
				);

		$count = \DB::selectOne(
					"select count(*) as total from notice n inner join entity e on e.id = n.id ". $where,
					$search ? array('search'=>'%'.$search.'%') : array()
					
				);

		return ['total'=>$count->total,'data'=>$items,'success'=>true];
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function create()
	{
		$attributes = \Input::all();
		$attributes['entity_type_id'] = Entity::ENTITY_TYPE_NOTICE;
        $user = OauthToken::find(\Input::get('access_token'))->user()->first();
        $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
        $attributes['society_id'] = $society_id;



        $validator = \Validator::make(
        $attributes,
        array(
            'title' => 'required | min:5',
            'text'=>'required',
            'status'=>'required',
            'type'=>'required',
            'expiry_date'=>'required',
            ),
        array(
            'title.required' => 'Title is required',
            'title.min' => 'At least 5 characters required',
            'text.required' => 'Description is required',
            'status.required'=>'Publish status is required',
            'type.required'=>'Notice type is required',
            'expiry_date.required'=>'Expiry date is required',
            )
        );

        if ($validator->fails())
            return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];

        $entity = new Entity();
        $entity->user()->associate($user);
		$entity->fill($attributes);
		$entity->save();
		 

		$attributes['id'] = $entity->id;
        $notice = new Notice();
        $notice->fill($attributes);
		$notice->save();
		
		 if($attributes["type"]=="3"  && !empty($attributes['role_id']))
		  {
					foreach ($attributes['role_id'] as $roles_id){
						$role = AclRole::find($roles_id);
						$notice_attendee = new NoticeAttendee(['notice_id' => $notice->id]);
						$notice_attendee->role()->associate($role);
						$notice_attendee->save();
					}	
			}
		return ['msg'=>'Notice created successfully','success'=>true];
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function item($id)
	{
        //$pdf = PDF::loadView('notice.print_view', ['id'=>$id]);
        //return $pdf->download('invoice.pdf');



		$item = \DB::selectOne(
				'select e.*,n.*,u.first_name,u.last_name  from entity e inner join notice n on n.id = e.id inner join users u on u.id = e.user_id where n.id = :id limit 1'
				,['id'=>$id]);

		return $item;
	}
	
    public function noticeattendee($id)
	{
     	$item = \DB::select(
				'select *  from notice_attendee  where notice_id = :id'
				,['id'=>$id]);
		return $item;
	}
	

        /**
	 * Edit Notice Form.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{

		$attributes = \Input::all();
		$attributes['entity_type_id'] = Entity::ENTITY_TYPE_NOTICE;

                $entity = Entity::where('id','=',$id)
				->where('entity_type_id','=',Entity::ENTITY_TYPE_NOTICE)
				->firstOrFail();

		if (!$entity)
			return ['error'=>'Notice not found with id: '.$id,'success'=>false];

                $validator = \Validator::make(
                $attributes,
                array(
                    	'title' => 'required | min:5',
                    	'text'=>'required',
                    	'status'=>'required',
                    	'type'=>'required',
                    	'expiry_date'=>'required',
                    ),
                array
					(
                    	'title.required' => 'Title is required',
                    	'title.min' => 'At least 5 characters required',
                    	'text.required' => 'Description is required',
                    	'status.required'=>'Publish status is required',
                    	'type.required'=>'Notice type is required',
                    	'expiry_date.required'=>'Expiry date is required',
                    )
                );
                if ($validator->fails())
                    return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];

                $entity->fill($attributes);
                $entity->save();
                $notice = Notice::find($entity->id);
                $notice->fill($attributes);
				$notice->save();
				
				if($attributes["type"]=="3"  && !empty($attributes['role_id']))
		       {
                    
                    NoticeAttendee::where('notice_id','=',$notice->id)->delete();
					foreach ($attributes['role_id'] as $roles_id){
						$role = AclRole::find($roles_id);
						$notice_attendee = new NoticeAttendee(['notice_id' => $notice->id]);
						$notice_attendee->role()->associate($role);
						$notice_attendee->save();
					}	
			}


		return ['msg'=>'Notice updated successfully','success'=>true];
	}
}
