<?php 

namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Requests;
use ApartmentApi\Http\Controllers\Controller;

use Illuminate\Http\Request;
use ApartmentApi\Models\Type;

class TypeController extends ApiController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function TypeList()
	{
            $results = Type::orderBy('type', 'asc')->paginate(5)->toArray();
            return $this->presentor->make200Response('Successfully loaded.', $results);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
            if($request->has('id')) 
            {
                $saveType = Type::where('type','=',$request->get('type'))
                            ->where('id','<>',$request->get('id'))                        
                            ->first();
                if ($saveType)
                {
                    return $this->presentor->make400Response('Type \''.$request->get('type').'\' already exists.');
                }
               
                $saveType = Type::find($request->get('id'));
            }
            else
            {
                $saveType = Type::where('type','=',$request->get('type'))                                                
                                ->first();
                if ($saveType)
                {
                    return $this->presentor->make400Response('Type \''.$request->get('type').'\' already exists.');
                }
                $saveType = new Type();
            }
            
            
            
            $saveType->fill($request->all());
            $saveType->save();
            return $this->presentor->make200Response('Type saved successfully.', $saveType);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
            $results = Type::where('id', $id)->first();
            return $this->presentor->make200Response('Successfully loaded.', $results);
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
	public function Duplicate(Request $request)
	{
            $duplicateType = Type::where('type','=',$request->get('type')) 
                            ->where('id','<>',$request->get('id'))
                            ->first();
        
            if ($duplicateType) {
                return $this->presentor->make400Response('Type \''.$request->get('type').'\' already exists.');
                
            }
            else{
                return $this->presentor->make200Response('no duplicate found');
            } 
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$type = Type::find($id);
                    $type->delete();
                    return ['msg'=>'Type deleted successfully','success'=>true];
	}

}
