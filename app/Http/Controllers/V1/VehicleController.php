<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers\V1;

use Illuminate\Http\Requests;
use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Models\OauthToken;
use Illuminate\Http\Request;
use Input;

Class VehicleController extends ApiController {
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
	
         public function getVehicle_type()
        {
//             print_r("test vehicle");exit;
//            $oauthToken = OauthToken::find(\Input::get('access_token'));
//            $society_id = $oauthToken->society_id;
            $data = \DB::select("select * from vehicle_type ORDER BY type_name");
            return ['data'=>$data];
//            return $this->presentor->make200Response('Successfully loaded.', $data);
        }
}