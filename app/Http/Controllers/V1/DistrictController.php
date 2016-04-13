<?php namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Requests;
use ApartmentApi\Http\Controllers\Controller;

use Illuminate\Http\Request;
use ApartmentApi\Models\District;
use ApartmentApi\Models\Region;

class DistrictController extends ApiController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
            $search = $request->get('search');
            if(strlen($search) == 0) 
            {
                 $districtList = District::join('state', 'district.state_id', '=', 'state.id')
                                        ->join('division', 'district.division_id', '=', 'division.id')
                                        ->join('region', 'district.region_id', '=', 'region.id')
                                        ->select('state.name as state','division.name as division','region.name as region','district.*')
                                        ->orderBy('name', 'asc');
                 
                               
            } else 
            {
               $districtList = District::join('state', 'district.state_id', '=', 'state.id')
                                        ->join('division', 'district.division_id', '=', 'division.id')
                                        ->join('region', 'district.region_id', '=', 'region.id')
                                        ->select('state.name as state','division.name as division','region.name as region','district.*')                      
                                        ->where('district.name','like', "%$search%")
                                        ->orderBy('name', 'asc');
                                
            }
            if($districtList) {
               return $request->get('per_page') === 'unlimited' ? $districtList->get() : $districtList->paginate(5);
            }else {
              return ['message'=>'not found'];
            } 
            
            return $request->get('per_page') === 'unlimited' ? $districtList->get() : $districtList->paginate(5);    
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
            $attributes = \Input::all();
                $validator = \Validator::make(
                    $attributes,
                            array(
                                'name' => 'Required'
                            )
                );

                if ($validator->fails()){
                    return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];
                }
                
            if($request->has('id')) 
            {
                 $addDistrict = District::where('name','=',$request->get('name'))
                                        ->where('id','<>',$request->get('id'))
                                        ->where('region_id','=',$request->get('region_id'))
                                        ->first();
                 if ($addDistrict) {
                    return $this->presentor->make400Response('District \''.$request->get('name').'\' already exists.');
                }
                
                $addDistrict = District::find($request->get('id'));
                $addDistrict->fill($request->all());
                $addDistrict->save();

            } else 
            {
                $addDistrict = District::where('name','=',$request->get('name'))
                                ->where('region_id','=',$request->get('region_id'))
                                ->first();
                
                 if ($addDistrict) {
                    return $this->presentor->make400Response('District \''.$request->get('name').'\' already exists.');
                }
                
                $addDistrict = new district();
                $addDistrict->fill($request->all());
                $addDistrict->save();
            }
            return $this->presentor->make200Response('Division saved successfully.', $addDistrict);
	
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Request $request,$id)
	{
            $regionId = $request->get('region_id');
            $districtList = District::join('division', 'district.division_id', '=', 'division.id')
                           ->join('state', 'district.state_id', '=', 'state.id')
                           ->join('region', 'district.region_id', '=', 'region.id')
                           ->select('state.name as state','division.name as division','region.name as region','district.*')
                           ->where('district.id','=',$id)
                           ->where('region.id','=',$regionId)
                           ->first();   
            return $this->presentor->make200Response('Successfully loaded.', $districtList);//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
        public function search(Request $request)
	{
            $search = $request->get('search');
            if(strlen($search) == 0) 
            {
                 $districtList = District::join('region', 'district.region_id', '=', 'region.id')
                                ->select('region.name as region','district.*') 
                                ->orderBy('name', 'asc')
                                ->paginate(5)->toArray(); //page pagination
            } else 
            {
               $districtList = District::join('region', 'district.region_id', '=', 'region.id')
                                ->select('region.name as region','district.*')
                                ->where('district.name','like', "%$search%")
                                ->orderBy('name', 'asc')
                                ->paginate(5)->toArray(); //search pagination
            }
            if($districtList) {
                return $this->presentor->make200Response('Successfully loaded.', $districtList);
            }else {
              return ['message'=>'not found'];
            }
        }

	public function destroy(Request $request,$id)
	{
//            $state_id = $request->get('state_id');
//            $division_id = $request->get('division_id');
//            $region_id = $request->get('region_id');
//            $results = District::where('state_id','=',$state_id)                       
//                        ->count();
//             if($results > 0) {
//			return ['msg'=>'Unable to Delete as this District as it is assigned to some State,District or Region.' ,'success'=>false];
//                }
//                
            $district = District::find($id)
                        ->delete(); 
             return ['msg'=>'Successfully Deleted' ,'success'=>true];
	}
        
          public function CheckDuplicateRegion(Request $request){
            
            $duplicate = District::where('name','=',$request->get('name'))
//                        ->where('id','<>',$request->get('id'))
                        ->where('region_id','=',$request->get('region_id'))
                        ->first();
        
            if ($duplicate) {
                return $this->presentor->make400Response('District\''.$request->get('name').'\' already exists.');
                
            }
            else{
                return $this->presentor->make200Response('no duplicate found');
            } 
        }
        
        public function listDivisionRegion($id) {
            $region = Region::where('division_id','=',$id)
                                ->orderBy('name', 'asc')
                                ->get();
                      
        
        return $this->presentor->make200Response('Successfully loaded.', $region);
        }
}
