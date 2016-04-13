<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Models\FlatParking;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\ParkingConfig;
use ApartmentApi\Models\ParkingSlot;
use ApartmentApi\Repositories\FlatParkingRepository;
use Illuminate\Http\Request;
use Input;

Class ParkingController extends ApiController {
    protected $input;
    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */

         public function createParkingConfig()
         {
            $attributes = \Input::all();

            $validator = \Validator::make(
                $attributes,
                array('total_slot' => 'required','slot_name_prefix'=>'required')
            );
            if ($validator->fails())
                return ['msg'=>'Input errors','input_errors'=>$validator->messages()];

            $total_slot = $attributes['total_slot'];
            $slot_name_prefix = $attributes['slot_name_prefix'];
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            $attributes['society_id'] = $society_id;

            $parking_config = new ParkingConfig();
            $parking_config->fill($attributes);
            $parking_config->save();
            for($i=1; $i<=$total_slot;$i++)
            {
                $parking_slot = new ParkingSlot();
                $parking_slot->slot_name = $slot_name_prefix.'_'.$i;
                $parking_slot->category_id = $attributes['category_id'];
                $parking_slot->parking_config_id = $parking_config->id;
                $parking_slot->society_id = $society_id;
                $parking_slot->save();
            }
            return ['msg'=>'Slot created successfully!','success'=>true];
//
         }

         public function getAllSlots()
         {
             $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            $data = \DB::select("select * from parking_slot where society_id ='".$society_id."' and status='1' ORDER BY slot_name");
             return ['data'=>$data];

//            return $this->presentor->make200Response('Successfully loaded.', $data);
         }

//         Used to allot parking slots to flats

         public function createParkingSlots()
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

           public function getSlotsList()
           {
               $oauthToken = OauthToken::find(\Input::get('access_token'));
                $society_id = $oauthToken->society_id;
           
                
            
            $search = \Input::get('search',null);
            $where = 'parking_slot.society_id="'.$society_id.'"';
//            print_r($where.'............');
            $sort = '';
            $whereSep = '';
            $bindings = array();
            
            if (\Input::get('search',null)) {
                $where .= ' and (parking_slot.slot_name like :slot_name)';
//                print_r($where);exit;
                $whereSep = true;
                $bindings['slot_name'] = '%'.\Input::get('search').'%';
//                $bindings['venue'] = '%'.\Input::get('search').'%';
            }
            $where = $where ? ' where '.$where : '';

            if (\Input::get('sort',null)){
                $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','asc').' ';
            }
//            $results = \DB::select("select meeting.*,users.first_name as user_name from meeting INNER JOIN users ON meeting.user_id=users.id $where $sort limit :limit offset :offset",
                     $results = \DB::select("SELECT parking_slot.id, parking_slot.slot_name,parking_slot.status,parking_category.category_name,if(block.block is not null  ,concat(flat.flat_no,'-',block.block,'-',society.name),concat(flat.flat_no,'-',society.name)) as flat FROM parking_slot"
                                . " INNER JOIN parking_category ON parking_slot.category_id = parking_category.id "
                               
                                . "LEFT JOIN flat_parking ON flat_parking.parking_Slot_id = parking_slot.id "                             
                                . "LEFT JOIN flat ON flat_parking.flat_id=flat.id "
                                . "LEFT JOIN user_society ON user_society.flat_id = flat_parking.flat_id "
                                ."LEFT JOIN society ON society.id = user_society.building_id "
                                ."LEFT JOIN block ON block.id = user_society.block_id "
                                
                                ."$where group by slot_name $sort  limit :limit offset :offset",
                            array_merge($bindings,array(
                                    'limit'=>\Input::get('limit',5),
                                    'offset'=>\Input::get('offset',0)
                                    )
                            )
                        );
//                     print_r($results);exit;
            $count = \DB::selectOne(
				"select count(parking_slot.id) total from parking_slot
				 $where ",
					$bindings
				);
		return ['total'=>$count->total,'data'=>$results];
           }


        public function RemoveSlot()
        {
            $id = \Input::get('id');
            FlatParking::where('parking_slot_id','=',$id)->delete();
            $parking_slot = ParkingSlot::find($id);
            $parking_slot->status = "1";
            $parking_slot->save();
            return ['msg'=>'slot with id '.$id.' deleted successfully.','success'=>true];
        }

        public function getParkingConfig()
        {
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            $id = \Input::get('id');
            $data = \DB::selectOne("select * from parking_config where society_id='".$society_id."' and category_id = '".$id."' limit 1");
            return['data'=>$data];
        }

        public function getFlatParking($id,Request $request, FlatParkingRepository $parkingRepo)
        {
            $queries = collect(array_merge($request->all(), ['id' => $id]));

            $data = $parkingRepo->searchFlatParking($queries);
            return $this->presentor->make200Response('Successfully loaded.', $data);
        }

        public function searchParkingSlots(Request $request)
        {
             $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            $search 	= $request->get('search');
            $sql = <<<EOF

		select slot_name from parking_slot
		where slot_name like :search and society_id like :society_id and status = 1
EOF;
            $slots = \DB::select($sql,['search'=>'%'.$search.'%','society_id'=>$society_id]);
            if($slots){
                return array_values($slots);
            }else{
               return ['success'=>false,'msg'=>'No results found'];
            }
        }
}
