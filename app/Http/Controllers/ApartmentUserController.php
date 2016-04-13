<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\ApartmentUser;

Class ApartmentUserController extends Controller {
    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('rest');
	}
	
	public function getList() {
		
		// getting list by using model
		$results = ApartmentUser::all(); // columns to retreive
		
		// getting list By using plain query
		//$results = DB::select('select * from post limit 15 order by created_at desc');
		
		//return response()->json(['testdata'=>'test data returned from list action']);
               // print_r($results);exit;
		return $results;
		
	}
	
	public function item($id) {
		
		$post = ApartmentUser::find($id);
		
		if (!$post)
			return ['msg'=>'user doesnot exist with id - '.$id];
		
		return $post;
	}
//        public function create() {
//		
//		// get all posted form data
//		$attributes = \Input::all();
//		// sample validation
//		$validator = \Validator::make(
//				$attributes,
//				array('name' => 'required|min:5','email'=>'required|email','password'=>'required','description'=>'required|min:5')
//                               
//		);
//		
//		if ($validator->fails()){
//			return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
//                }
//                
//		$attributes['password'] = \Hash::make($attributes['password']);
//                //$attributes['active_status'] = "0";
//		Users::create($attributes);
//		
//		return ['msg'=>'user created successfully'];
//	}
        /// used for both update and delete action
        public function updateOrCreate($id = null) {
		
		// get all posted form data
		$attributes = \Input::all();
		
		// sample validation
		$validator = \Validator::make(
				$attributes,
				array('designation' => 'required|','address'=>'required')
                               
		);
		
		if ($validator->fails())
			return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
		
		$attributes['user_id'] = 1;
                ApartmentUser::updateOrCreate(['apartment_user_id'=>$id],$attributes);
		if($id==null)
                {
                    return ['msg'=>' apartment user created successfully'];
                }else{
                    
                    return ['msg'=>'apartment user updated successfully'];
                }
	}
        public function delete($id) {
		
		// for completely removing entity from database
		//Entity::destroy($ids);
		
		// for soft deleting
		$post = ApartmentUser::find($id);
                if($post)
                {
                   //print_r($post);die;
                    $post->delete();
                    return ['msg'=>'apartment user deleted successfully'];
                }else{
                    
                     return ['msg'=>'No data found'];
                }
	}
        public function restore($id) {
		
		ApartmentUser::withTrashed()->where('id','=',Input::get('apartment_user_id'))->restore();
		
		return ['msg'=>'apartment user restored successfully'];
	}
}