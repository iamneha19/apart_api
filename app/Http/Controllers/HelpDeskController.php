<?php
namespace ApartmentApi\Http\Controllers;
use Illuminate\Http\Request;
use ApartmentApi\Models\Ticket;
use ApartmentApi\Models\TicketCategory;
use ApartmentApi\Models\TicketNote;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\Flat;
use ApartmentApi\Models\Category;

class HelpDeskController extends Controller
{
	public function __construct() {

		$this->middleware('rest');
	}

	public function createOrUpdate(Request $request) {

		$attributes = $request->all();
		$token = OauthToken::find($request->get('access_token'));
		$user = $token->user()->first();
		$society = $token->society()->first();

		if ($request->has('id')) {

			$ticket = Ticket::find($request->get('id'));
		} else {

			$ticket = new Ticket();
		}

		$category = Category::find($request->get('category_id'),['id','name']);
		$flat = Flat::find($request->get('flat_id'),['id','flat_no']);

		$ticket->fill($attributes);
		$ticket->createdBy()->associate($user);
		$ticket->category()->associate($category);
		$ticket->flat()->associate($flat);
		$ticket->society()->associate($society);
		$ticket->save();
		$ticket->first_name = $user->first_name;
		$ticket->category_name = $category->name;
		$ticket->flat_no = $flat->flat_no;
		unset($ticket->createdBy);
		unset($ticket->category);
		unset($ticket->flat);
		$ticket->ticket_status = 'New';

		return ['success'=>true,'msg'=>'Ticket created succesfully','data'=>$ticket];
	}

	public function createOrUpdateCategory(Request $request) {

		$dup = TicketCategory::where('category_name','=',$request->get('category_name'))->first();

		if ($dup) {
			return ['success'=>false,'msg'=>'category with name - '.$request->get('category_name').' already exits'];
		}

		$attributes = $request->all();


		$token = OauthToken::find($request->get('access_token'));
		$user = $token->user()->first();
		$society = $token->society()->first();

		if ($request->has('id')) {
			$ticketCategory = TicketCategory::find($request->get('id'));
		} else {
			$ticketCategory = new TicketCategory();
		}

		$ticketCategory->fill($attributes);
		$ticketCategory->user()->associate($user);
		$ticketCategory->society()->associate($society);
		$ticketCategory->save();
		$ticketCategory->first_name = $user->first_name;
		unset($ticketCategory->user);

		return ['success'=>true,'msg'=>'Ticket Category created succesfully','data'=>$ticketCategory];
	}

	public function CategoryList() {

		return \DB::select('select id,category_name,description from ticket_category limit 20');
	}

  public function All_categoryList() {

		return \DB::select('select id,category_name,description from ticket_category order by category_name');
	}
	public function ticketList(Request $request) {
		$limit = $request->get('limit',5);
		$token = OauthToken::find($request->get('access_token'));
		$user = $token->user()->first();
		$where = '';
		$bindings = ['society_id'=>$token->society_id];
		$admin = $token->hasRole('admin');
		$and = false;
		$offset = $request->get('offset',0);
		$where .= ' where ticket.society_id = :society_id and ';
		
		$type = \Input::get('issueType',1);
		if ( $type != 0){
			$type = $type == 1 ? 'New' : 'Closed';
			$where .= ' ticket.ticket_status = "' . $type . '" AND';
		}
		
		$search = \Input::get('search',null);	
		if ($search) {
			$where .= ' ticket.issue like :search AND';
			$bindings['search'] = '%'.$search.'%';
		}
		
		if (!$admin) {
			$where .= ' ticket.created_by = :user_id AND';
			$bindings['user_id'] = $token->user_id;
			$and = true;
		}
		$where .= " true ";

		$sort = "";
		if (\Input::get('sort',null)){
        $sort =  \Input::get('sort',null);
         if($sort == 'flat'){
             $sort = ' order by ticket.id '.\Input::get('sort_order','desc').' ';
         }else{
            $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','desc').' ';
         }

     }else {
			  $sort = ' order by ticket.id '.\Input::get('sort_order','desc').' ';
     }
	 

		$sql = <<<EOF
		select ticket.id,left(ticket.issue,200) issue,date_format(ticket.created_at,'%d-%m-%Y') as created_at,date_format(ticket.updated_at,'%d-%m-%Y') as updated_at,ticket.ticket_status,ticket.is_urgent,
		category.name as category_name,users.first_name,if(block.block is not null  ,concat(flat.flat_no,'-',block.block,'-',society.name),concat(flat.flat_no,'-',society.name)) as flat,ticket.ticket_status as ticketStatus 
			from ticket
			inner join category on category.id = ticket.category_id
			inner join flat on flat.id = ticket.flat_id
		    INNER JOIN user_society ON user_society.flat_id = ticket.flat_id 
            INNER JOIN society ON society.id = user_society.building_id 
			INNER JOIN block ON block.id = user_society.block_id 

			inner join users on users.id = ticket.created_by $where
		 GROUP BY ticket.id $sort  limit $limit offset $offset
EOF;

		$countSql = <<<EOF
		select count(ticket.id) as count
			from ticket
			inner join category on category.id = ticket.category_id
			inner join flat on flat.id = ticket.flat_id
			inner join users on users.id = ticket.created_by $where
EOF;


		return [
				'data' => \DB::select($sql,$bindings),
				'count' => \DB::selectOne($countSql,$bindings)
			];
	}

	    public function ticket($id) {

		$sql = <<<EOF
		select ticket.id,ticket.issue,ticket.created_at,flat.flat_no,ticket.ticket_status,
		category.name as category_name,users.first_name
			from ticket
			inner join category on category.id = ticket.category_id
			inner join flat on flat.id = ticket.flat_id
			inner join users on users.id = ticket.created_by
		where ticket.id = :id;
EOF;

		return \DB::selectOne($sql,['id'=>$id]);

	}

	public function ticketNotes($ticketId) {
		$sql = <<<EOF
		select ticket_note.id,ticket_note.note,users.first_name,ticket_note.status_update from ticket_note
				inner join users on users.id = ticket_note.user_id where ticket_id = :id
EOF;

		return \DB::select($sql,['id'=>$ticketId]);

	}

	public function saveAdminTicketNote(Request $request,$ticketId) {

		$ticket = Ticket::find($ticketId);

		$user = OauthToken::find($request->get('access_token'))->user()->first();

		if (!$ticket)
			return ['status'=>false,'msg'=>'ticket does not exist'];

		$user = OauthToken::find($request->get('access_token'))
		->user()->first();

		$note = new TicketNote();

		if ($request->get('status',null)) {
			$note->status_update = 'Ticket status changed to '.$request->get('status').' by '.$user->first_name.' '.$user->last_name;
			$ticket->ticket_status = $request->get('status');
		}

		$note->note = $request->get('note');
		$note->user()->associate($user);
		$note->ticket()->associate($ticket);
		$note->save();
		$note->first_name = $note->user->first_name;
		unset($note->user);
		$ticket->save();

		return ['success'=>true,'msg'=>'Ticket note saved successfully','data'=>$note];

	}


	public function saveTicketNote(Request $request,$ticketId) {

		$ticket = Ticket::find($ticketId,['id']);

		if (!$ticket)
			return ['status'=>false,'msg'=>'ticket does not exist'];

		$user = OauthToken::find($request->get('access_token'))
				->user()->first();

		$note = new TicketNote();

		$note->note = $request->get('note');
		$note->user()->associate($user);
		$note->ticket()->associate($ticket);
		$note->save();
		$note->first_name = $note->user->first_name;

		unset($note->user);

		return ['success'=>true,'msg'=>'Ticket note saved successfully','data'=>$note];

	}


	public function ticketCount(Request $request) {

		$token = OauthToken::find($request->get('access_token'));
		$societyId = $token->society_id;
        $status = $request->get('status');
        $where = '';

        if (!$status) {

            $where .= ' and ticket_status <> :status';
        } else {

             $where .= ' and ticket_status = :status';
        }
        //dd($where);
		$results = \DB::selectOne ('select count(ticket.id) as ticket_count from ticket where society_id = :societyId '.$where,
                    ['societyId'=>$societyId ,'status'=>'Closed']);

		return $results;

	}


}
