<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers;

use Illuminate\Http\Request;
use ApartmentApi\Models\TaskCategory;
use ApartmentApi\Models\OauthToken;
use Input;

Class TaskCategoryController extends Controller {
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
	
    public function getCategoriesList() {
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $society_id = $oauthToken->society_id;
        $search = $this->input->get('search',null);
        $where = 'task_category.society_id='.$society_id;
        $sort = '';
        $whereSep = '';
        $bindings = array();
        
        if ($this->input->get('search',null)) {
            $where .= ' and task_category.category_name like :category_name';
            $whereSep = true;
            $bindings['category_name'] = '%'.\Input::get('search').'%';
        }
        $where = $where ? ' where '.$where : '';
        
        if (\Input::get('sort',null)){
			$sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','desc').' ';
		}
		
        
        $data = \DB::select("select * from task_category $where $sort limit :limit offset :offset",
                array_merge($bindings,array(
                                    'limit'=>\Input::get('limit',10),
                                    'offset'=>\Input::get('offset',0)
                                    )
                                )
                            );
	$count = \DB::selectOne(
				"select count(task_category.id) total from task_category
				 $where ",
					$bindings
				);        
        return array(
                        'total'=>$count->total,
                        'data'=>$data
                    );
		
	}
	
	public function getCategory($id) {
		
		$post = TaskCategory::find($id);
		
		if (!$post)
			return ['msg'=>'Task category doesnot exist with id - '.$id];
		
		return $post;
	}
        /// used for both update and delete action
//        public function updateOrCreate($id = null) {
//		
//		// get all posted form data
//		$attributes = \Input::all();
////                print_r($attributes);exit;
//                
//                
//                $oauthToken = OauthToken::find(\Input::get('access_token'));
//                $society_id = $oauthToken->society_id;
//		$user_id = $oauthToken->user()->first();
//                $attributes['created_by'] = $user_id->id;
//                $attributes['society_id'] = $society_id;
//		// sample validation
//		$validator = \Validator::make(
//				$attributes,
//				array('category_name' => 'required')
//                               
//		);
//		
//		if ($validator->fails())
//			return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
//                $category = TaskCategory::where('category_name','=',$attributes['category_name'])->where('society_id','=',$attributes['society_id'])->first();
//                if($category == '')
//                {
//                    return ['success'=>true,'msg'=>'category_name is not taken'];
//                }else{
//                    return ['success'=>false,'duplicate_category'=>'duplicate_entry','msg'=>'category_name is already exists!'];
//                }
//                
//                TaskCategory::updateOrCreate(['id'=>$id],$attributes);
//		if($id==null)
//                {
//                    return ['success'=>true,'msg'=>'Task category created successfully'];
//                }else{
//                    
//                    return ['success'=>true,'msg'=>'Task category updated successfully'];
//                }
//	}
        
        /** Create category**/
        public function createCategory() {
            $attributes = \Input::all();
//            print_r($attributes);exit;
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            $user_id = $oauthToken->user()->first();
            $attributes['created_by'] = $user_id->id;
            $attributes['society_id'] = $society_id;
		// sample validation
            $validator = \Validator::make(
                            $attributes,
                            array('category_name' => 'required')

            );
		
            if ($validator->fails())
                return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
            $category = TaskCategory::where('category_name','=',$attributes['category_name'])->where('society_id','=',$attributes['society_id'])->first();
                if($category!=''){
                    return ['success'=>false,'duplicate_category'=>'duplicate_entry','msg'=>'category_name is already exists!'];
                }else{
                    $task_category = new TaskCategory();
//                    print_r($task_category);exit;
                    $task_category->fill($attributes);
                
                    $task_category->save();
		
                return ['msg'=>'Category created successfully','success'=>true];
                }
	}
        
        /** Update category**/
         public function updateCategory($id)
         {
            $attributes = \Input::all();
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $user = $oauthToken->user()->first();
            $society_id = $oauthToken->society_id;
                
            $task_category = TaskCategory::where('id','=',$id)
				->firstOrFail();
                if (!$task_category)
                    return ['error'=>'Category not found with id: '.$id,'success'=>false];
                
            $sql = 'select count(*) as total from task_category where society_id = :society_id and category_name = :category_name and id != :id';
            $result = \DB::selectOne($sql,['society_id'=>$society_id,'category_name'=>$attributes['category_name'],'id'=>$id]);
                if($result->total){
                    return ['success'=>false,'duplicate_category'=>'duplicate_entry','msg'=>'This category already exists'];
                }
                
		$task_category->fill($attributes);
		$task_category->save();
		
                return ['msg'=>'Category updated successfully','success'=>true];
         }
        
        
        
        
        public function check_category()
        {
            $society_id = $_POST['society_id'];
            $category_name = $_POST['category_name'];
            $category = TaskCategory::where('category_name','=',$category_name)->where('society_id','=',$society_id)->first();
             if($category == '')
             {
                 return ['success'=>true,'msg'=>'category_name is not taken'];
             }else{
                 return ['success'=>false,'msg'=>'category_name is already exists!'];
             }
        }
         public function getAllCategories()
        {
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            $data = \DB::select("select * from task_category where society_id = :society_id ORDER BY category_name",['society_id'=>$society_id]);
            return array(
                'data'=>$data,
            );
        }
}