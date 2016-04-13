<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Http\Controllers\Controller;
use Illuminate\Http\Request;
use ApartmentApi\Models\ParkingSlot;
use ApartmentApi\Models\OauthToken;
use Input;

Class ParkingSlotController extends Controller {
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
     public function getAllSlots()
     {
         
         
     }
    
    public function create()
    {
        $attributes = \Input::all();
            print_r($attributes);exit;
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
}