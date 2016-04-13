<?php namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Requests;
use ApartmentApi\Http\Controllers\Controller;

use Illuminate\Http\Request;
use ApartmentApi\Models\Region;
use ApartmentApi\Models\District;

class RegionController extends ApiController {

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
                 $regionList = Region::join('state', 'region.state_id', '=', 'state.id')
                                    ->join('division', 'region.division_id', '=', 'division.id')                                   
                                    ->select('state.id','division.id','state.name as state','division.name as division','region.*')
                                    ->orderBy('name', 'asc');
                
            } else 
            {
               $regionList = Region::join('division', 'region.division_id', '=', 'division.id')
                                    ->join('state', 'region.state_id', '=', 'state.id')
                                    ->select('state.id','division.id','state.name as state','division.name as division','region.*')
                                    ->where('region.name','like', "%$search%")
                                    ->orderBy('name', 'asc');
                               
            }
            if($regionList) {
                 return $request->get('per_page') === 'unlimited' ? $regionList->get() : $regionList->paginate(5);
            }else {
              return ['message'=>'not found'];
            }
            
            return $request->get('per_page') === 'unlimited' ? $regionList->get() : $regionList->paginate(5); 
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
                $addRegion = Region::where('name','=',$request->get('name'))
                                        ->where('id','<>',$request->get('id'))
                                        ->where('division_id','=',$request->get('division_id'))
                                        ->first();
                 if ($addRegion) {
                    return $this->presentor->make400Response('Region \''.$request->get('name').'\' already exists.');
                }
                $addRegion = Region::find($request->get('id'));
                $addRegion->fill($request->all());
                $addRegion->save();

            } else 
            {
                $addRegion = Region::where('name','=',$request->get('name'))
                                ->where('division_id','=',$request->get('division_id'))
                                ->first();
                
                 if ($addRegion) {
                    return $this->presentor->make400Response('Region \''.$request->get('name').'\' already exists.');
                }
                $addRegion = new Region();
                $addRegion->fill($request->all());
                $addRegion->save();
            }
            return $this->presentor->make200Response('Division saved successfully.', $addRegion);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Request $request,$id)
	{
            $divisionId = $request->get('division_id');
            $regionList = Region::join('division', 'region.division_id', '=', 'division.id')
                           ->join('state', 'region.state_id', '=', 'state.id')
                           ->select('state.name as state','division.name as division','region.*')
                           ->where('region.id','=',$id)
                            ->where('division.id','=',$divisionId)
                           ->first();   
            return $this->presentor->make200Response('Successfully loaded.', $regionList);//
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
                 $regionList = Region::join('division', 'region.division_id', '=', 'division.id')
                               ->select('division.name as division','region.*')
                                ->orderBy('name', 'asc')
                                ->paginate(5)->toArray(); //page pagination
                
            } else 
            {
               $regionList = Region::join('division', 'region.division_id', '=', 'division.id')
                               ->select('division.name as division','region.*')
                                ->where('region.name','like', "%$search%")
                                 ->orderBy('name', 'asc')
                                ->paginate(5)->toArray(); //search pagination
            }
            if($regionList) {
                return $this->presentor->make200Response('Successfully loaded.', $regionList);
            }else {
              return ['message'=>'not found'];
            }
        }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy(Request $request,$id)
	{
            $results = District::where('region_id','=',$id)
                        ->count();
             if($results > 0) {
			return ['msg'=>'Unable to Delete as this Region as it is assigned to District' ,'success'=>false];
                }
                
            $region = Region::find($id)
                        ->delete(); 
            return ['msg'=>'Successfully Deleted' ,'success'=>true];
	}

        public function CheckDuplicateRegion(Request $request){
            
            $duplicate = Region::where('name','=',$request->get('name'))
//                        ->where('id','<>',$request->get('id'))
                        ->where('division_id','=',$request->get('division_id'))
                        ->first();
        
            if ($duplicate) {
                return $this->presentor->make400Response('Region\''.$request->get('name').'\' already exists.');
                
            }
            else{
                return $this->presentor->make200Response('no duplicate found');
            } 
        }
    
}
