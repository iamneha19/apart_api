<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Http\Controllers\Controller;
use Illuminate\Http\Request;
use ApartmentApi\Models\Task;
use ApartmentApi\Models\User;
use ApartmentApi\Models\TaskCategory;
use ApartmentApi\Models\OauthToken;
use Input;
use ApartmentApi\Models\Category;

Class TaskController extends Controller {
    protected $input;
    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
    public function __construct(Input $input)
    {
        $this->input = $input;
        $this->middleware('rest');
    }
	
    public function getTasksList() {
	// getting list by using
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $society_id = $oauthToken->society_id;
        
        $where = 'task.society_id='.$society_id . " and ";
        $sort = '';
        $whereSep = '';
		$bindings = array();
        if ( \Input::get('type',null) != 'A' ){
			$where .= $whereSep ? '' : '';
			$where .= ' task.type = :type' .  " and " ;
            $bindings['type'] = \Input::get('type') ;
		}
		if (\Input::get('search',null)) {
			$where .= ' task.title like :search AND';
			$bindings['search'] = '%'.\Input::get('search',null).'%';
		}
		$where .= ' true ';
		$where = $where ? ' where '.$where : '';
        if (\Input::get('sort',null)){
            $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','desc').' ';
        }
		
		$results = \DB::select("select task.*,IF(task.type='C','Closed',IF(task.type='O','Open',IF(task.type='R','Recur','null'))) as task_type,first_user.first_name as createdBy,second_user.first_name as assignTo,category.name as category,date_format(task.created_at,'%d-%m-%Y') as created_at from task "
                . "            INNER JOIN users as first_user ON task.created_by = first_user.id "
                . "            INNER JOIN users as second_user ON task.assign_to=second_user.id  "
                . "            INNER JOIN category ON task.task_category_id = category.id "
                . "            $where $sort limit :limit offset :offset",
								array_merge($bindings,array(
                                'limit'=>\Input::get('limit',20),
                                'offset'=>\Input::get('offset',0)))
                            
                        );
        $count = \DB::selectOne(
				"select count(task.id) total from task
				 $where ",
					$bindings
				);
        return ['data'=>$results,'total'=>$count->total];
    }
	
    public function getTask($id) {
	$post = \DB::selectOne("select task.*,IF(task.type='C','Closed',IF(task.type='O','Open',IF(task.type='R','Recur','null'))) as task_type,CONCAT(first_user.first_name,' ',first_user.last_name) as createdBy,CONCAT(second_user.first_name,' ',second_user.last_name) as assign_user,category.name as category from task "
                . "INNER JOIN users as first_user ON task.created_by=first_user.id "
                . "INNER JOIN users as second_user ON task.assign_to=second_user.id "
                . "INNER JOIN category ON task.task_category_id=category.id "
                . "where task.id = :id limit 1",['id'=>$id]);
	if (!$post){
            return ['msg'=>'Task doesnot exist with id - '.$id];
        }
	return $post;
	}

    public function create(){
        $attributes = \Input::all();
        $start_date =  $attributes['begin_on'];
        $end_date =  $attributes['due_on'];
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $society_id = $oauthToken->society_id;
        
        $assign_id = $attributes['assign_to'];
        $category_id = $attributes['task_category_id'];
        
        $user_details = User::find($assign_id,['email','first_name']);
        $category_details = Category::find($category_id,['name']);
        $user_id = $oauthToken->user()->first();
        $validator = \Validator::make(
            $attributes,
            array('title' => 'required','begin_on'=>'required','due_on'=>'required')             
            );
        if($start_date>$end_date)
        {
            
            return['msg'=>'start date must be less then end date'];
        }
        if ($validator->fails())
            return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
        $task = new Task();
        $task->user()->associate($user_id);
        $attributes['society_id'] = $society_id;
        $attributes['type']="O";
        $due_on = $attributes['due_on'];
        $begin_on = $attributes['begin_on'];
        $due_date = date('d-m-Y ',strtotime($due_on));
        $begin_date = date('d-m-Y ',strtotime($begin_on));
        $task->fill($attributes);
        $task->save();
        
        $data = array(
                        'created_by'=>$user_id->first_name.' '.$user_id->last_name,
                        'from_email'=>$user_id->email,
                        'to_email'=>$user_details->email,
                        'assign_to'=>$user_details->first_name.' '.$user_details->last_name,
                        'title'=>$attributes['title'],
                        'category'=>$category_details->name,
                        'due_on'=>$due_date,
                        'begin_on'=>$begin_date
                
                            );
                event(new \ApartmentApi\Events\TaskWasCreated($data));
            
        return ['msg'=>'Task created successfully'];
   }
   
    public function edit($id)
    {
        $type='';
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $user_id = $oauthToken->user()->first();
        $attributes = \Input::all();
        
        $created_by_id = $attributes['created_by'];
        $assign_id = $attributes['assign_to'];
        $category_id = $attributes['task_category_id'];
        
        $user_details = User::find($assign_id,['email','first_name','last_name']);
        $category_details = Category::find($category_id,['name']);
        $created_user = User::find($created_by_id,['email','first_name','last_name']);
        
        $validator = \Validator::make(
            $attributes,
            array('title' => 'required','task_category_id'=>'required','assign_to'=>'required','begin_on'=>'required','due_on'=>'required')             
            );
            if ($validator->fails())
            return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
        $task = Task::find($id);
        $task_type = $task->type;
        if(!$task)
        {
            return ['msg'=>'Task doesnot exist with id - '.$id];
        }
        $due_on = $attributes['due_on'];
        $begin_on = $attributes['begin_on'];
        $due_date = date('d-m-Y ',strtotime($due_on));
        $begin_date = date('d-m-Y ',strtotime($begin_on));
        $task->fill($attributes);
        $new_task_type = $task->type;
        if($task_type == $new_task_type)
        {
            $type == '';
        }else{
            
            $type = $new_task_type;
        }

        $task->save();
        $data = array(
                        'created_by'=>$created_user->first_name.' '.$created_user->last_name,
                        'change_by'=>$user_id->first_name.' '.$user_id->last_name,
                        'from_email'=>$user_id->email,
                        'to_email'=>$user_details->email,
                        'assign_to'=>$user_details->first_name.' '.$user_details->last_name,
                        'title'=>$attributes['title'],
                        'category'=>$category_details->category_name,
                        'due_on'=>$due_date,
                        'begin_on'=>$begin_date,
                        'type'=>$type
                
                            );
                event(new \ApartmentApi\Events\TaskWasUpdated($data));
        return ['msg'=>'Task updated successfully'];
   }
    public function getMyTasks()
    {
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $society_id = $oauthToken->society_id;
        $user_id = $oauthToken->user_id;
        $sort = '';
        $search = $this->input->get('search',null);
        $where = 'task.society_id="'.$society_id.'" and task.assign_to="'.$user_id.'"';
        $whereSep = '';
		$bindings = array();
		if ( \Input::get('type',null) != 'A' ){
			$where .= $whereSep ? '' : '';
			$where .= ' and task.type = :type';
            $bindings['type'] = \Input::get('type');
			if($bindings['type'] == 'O')
            {
               $where .= ' and task.due_on >= now()'; 
            }
		}
		if (\Input::get('search',null)) {
			$where .= ' AND task.title like :search ';
			$bindings['search'] = '%'.\Input::get('search',null).'%';
		}
        $where = $where ? ' where '.$where : '';
        if (\Input::get('sort',null)){
              $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','desc').' ';
        }
        $results = \DB::select("select task.*,IF(task.type='C','Closed',IF(task.type='O','Open',IF(task.type='R','Recur','null'))) as task_type,first_user.first_name as createdBy,second_user.first_name as assignTo,category.name as category from task"
                . " INNER JOIN users as first_user ON task.created_by=first_user.id "
                . "INNER JOIN users as second_user ON task.assign_to=second_user.id "
                . " INNER JOIN category ON task.task_category_id=category.id $where $sort limit :limit offset :offset",
                                        array_merge($bindings,array(
					'limit'=>\Input::get('limit',5),
					'offset'=>\Input::get('offset',0)
				))
                        );
        $count = \DB::selectOne(
				"select count(task.id) total from task
				 $where ",
					$bindings
				);
        return ['data'=>$results,'total'=>$count->total];
   }
    public function close()
    {
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $user_id = $oauthToken->user()->first();
        $data = \Input::all();
        $task = Task::find($data['task_id']);
        
        $assign_id = $task->assign_to;
        $assigned_user = User::find($assign_id,['email','first_name','last_name']);
        $title = $task->title;
        $task['type']="C";
        
        $task->save();
         $data = array(
                        'change_by'=>$user_id->first_name.' '.$user_id->last_name,
                        'from_email'=>$user_id->email,
                        'to_email'=>$assigned_user->email,
                        'assign_to'=>$assigned_user->first_name.' '.$assigned_user->last_name,
                        'title'=>$title,
                        'type'=>'C'
                
                            );
                event(new \ApartmentApi\Events\TaskWasUpdated($data));
        return["task closed successfully"];
   }
   public function oldTasks()
   {
       $oauthToken = OauthToken::find(\Input::get('access_token'));
       $society_id = $oauthToken->society_id;
       $user_id = $oauthToken->user_id;
       $search = $this->input->get('search',null);
       $sort = '';
        $where = 'task.society_id="'.$society_id.'" and task.assign_to="'.$user_id.'" and task.due_on < now()';
        $whereSep = '';
	$bindings = array();
        
        if ($this->input->get('search',null)) {
            $where .= ' and task.title like :title ';
            $whereSep = true;
            $bindings['title'] = '%'.\Input::get('search').'%';
        }
       
        if (\Input::get('type',null)) {
            $where .= $whereSep ? '' : '';
            $where .= ' and task.type = :type';
            $bindings['type'] = \Input::get('type');
        }
        if (\Input::get('sort',null)){
              $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','desc').' ';
        }
          $where = $where ? ' where '.$where : '';
           
        $results = \DB::select("select task.*,IF(task.type='C','Closed',IF(task.type='O','Open',IF(task.type='R','Recur','null'))) as task_type,first_user.first_name as createdBy,second_user.first_name as assignTo,category.name as category from task "
                . "INNER JOIN users as first_user ON task.created_by=first_user.id "
                . "INNER JOIN users as second_user ON task.assign_to=second_user.id  "
                . "INNER JOIN category ON task.task_category_id=category.id $where $sort limit :limit offset :offset",
                                        array_merge($bindings,array(
					'limit'=>\Input::get('limit',5),
					'offset'=>\Input::get('offset',0)
                                ))
                        );
        $count = \DB::selectOne(
				"select count(task.id) total from task
				 $where ",
					$bindings
				);
        return ['data'=>$results,'total'=>$count->total];
   }
}