<?php 

namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Requests;
use ApartmentApi\Http\Controllers\Controller;
use Illuminate\Http\Request;
use ApartmentApi\Models\Division;
use ApartmentApi\Models\Region;

class DivisionController extends ApiController {

	
	public function create()
	{
                
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
            $state_id = (int)$request->get('state_id');       
            if($request->has('id')) 
            {
                $addDivision = Division::where('name','=',$request->get('name'))
                                        ->where('id','<>',$request->get('id'))
                                        ->where('state_id','=',$state_id)
                                        ->first();
                 if ($addDivision) {
                    return $this->presentor->make400Response('Division \''.$request->get('name').'\' already exists.');
                }
                
                $addDivision = Division::find($request->get('id'));
                $addDivision->fill($request->all());
                $addDivision->save();

            } else 
            {
                $addDivision = Division::where('name','=',$request->get('name'))
                                ->where('state_id','=',$request->get('state_id'))
                                ->first();
                
                 if ($addDivision) {
                    return $this->presentor->make400Response('District \''.$request->get('name').'\' already exists.');
                }
                $addDivision = new Division();
                $addDivision->fill($request->all());
                $addDivision->save();
            }
            return $this->presentor->make200Response('Division saved successfully.', $addDivision);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Request $request,$id)
	{
            $stateId = $request->get('state_id');
            $divisionList = Division::join('state', 'division.state_id', '=', 'state.id')
                            ->select('state.name as state','division.*')
                            ->where('state.id','=',$stateId)
                            ->where('division.id','=',$id)
                            ->first();     
            return $this->presentor->make200Response('Successfully loaded.', $divisionList);
	}

        public function listDivision(Request $request)
	{
            $search = $request->get('search');
            if(strlen($search) == 0) 
            {
                 $divisionList = Division::join('state', 'division.state_id', '=', 'state.id')
                                ->select('state.name as state','division.*')
                                ->orderBy('name', 'asc');
                               
            } else 
            {
               $divisionList = Division::join('state', 'division.state_id', '=', 'state.id')
                                ->select('state.name as state','division.*')
                                ->where('division.name','like', "%$search%")
                                ->orderBy('name', 'asc');
                                
            }
            if($divisionList) {
               return $request->get('per_page') === 'unlimited' ? $divisionList->get() : $divisionList->paginate(5);
            }else {
              return ['message'=>'not found'];
            }
            
            return $request->get('per_page') === 'unlimited' ? $divisionList->get() : $divisionList->paginate(5);           
                         
        }
	
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
                 $divisionList = Division::join('state', 'division.state_id', '=', 'state.id')
                                ->select('state.name as state','division.*')
                                ->orderBy('name', 'asc')
                                ->paginate(5)->toArray(); //page pagination
            } else 
            {
               $divisionList = Division::join('state', 'division.state_id', '=', 'state.id')
                                ->select('state.name as state','division.*')
                                ->where('division.name','like', "%$search%")
                                ->orderBy('name', 'asc')
                                ->paginate(5)->toArray(); //search pagination
            }
            if($divisionList) {
                return $this->presentor->make200Response('Successfully loaded.', $divisionList);
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
            $results = Region::where('division_id','=',$id)                       
                                ->count();
             if($results > 0) {
			return ['msg'=>'Unable to Delete as this Division as it is assigned to Region.' ,'success'=>false];
                }
                
            $division = Division::find($id)
                        ->delete(); 
            return ['msg'=>'Successfully Deleted' ,'success'=>true];
		
	}

        public function CheckDuplicateDivision(Request $request){
            
        $duplicate = Division::where('name','=',$request->get('name'))
//                        ->where('id','<>',$request->get('id'))
                        ->where('state_id','=',$request->get('state_id'))
                        ->first();
        
            if ($duplicate) {
                return $this->presentor->make400Response('Division\''.$request->get('name').'\' already exists.');
                
            }
            else{
                return $this->presentor->make200Response('no duplicate found');
            } 
    }
    public function listStateDivision($id) {
        
        $division = Division::where('state_id','=',$id)
                                ->orderBy('name', 'asc')
                                ->get();
                      
        
        return $this->presentor->make200Response('Successfully loaded.', $division);
    }
}
