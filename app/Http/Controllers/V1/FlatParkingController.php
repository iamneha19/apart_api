<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers\V1;

use Illuminate\Http\Request;
use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\ParkingConfig;
use ApartmentApi\Models\ParkingSlot;
use ApartmentApi\Models\FlatParking;
use Illuminate\Http\Requests;

use Input;

Class FlatParkingController extends ApiController {
    protected $input;
    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
    public function __construct(Input $input)
    {
        $this->input = $input;
//        $this->middleware('rest');
    }
	
         public function create()
        {
             $attributes = Input::all();
             $validator = \Validator::make(
                $attributes,
                array('parking_slot_id' => 'required','vehicle_type'=>'required')
            );
            if ($validator->fails())
                return ['msg'=>'Input errors','input_errors'=>$validator->messages()];
            
             $vehicle_type = $attributes['vehicle_type'];
             $slot_id = $attributes['parking_slot_id'];
             
            
             $flat_parking = new FlatParking;
             $flat_parking->fill($attributes);
             $flat_parking->save();
             $parking_slot = ParkingSlot::find($slot_id);
             $parking_slot->vehicle_type = $vehicle_type;
             $parking_slot->status = "0";
             $parking_slot->save();
             return ['msg'=>'Slot alloted successfully!','success'=>true];
             
             
        }
}