<?php 

namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Requests;
use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\Category;
use Illuminate\Http\Request;
use ApartmentApi\Models\Society;
use ApartmentApi\Models\File;
use ApartmentApi\Models\Meeting;
use ApartmentApi\Models\Task;
use ApartmentApi\Models\Ticket;

class CategoryController extends ApiController 
{
    public function defaultSuperAdminTypeShow(Request $request)
    {
        $results = Category::where('type', 'society')->orderBy('name', 'asc')->paginate($request->get('per_page'))->toArray();
        return $this->presentor->make200Response('Successfully loaded.', $results);
    }

    public function defaultAdminTypeShow(Request $request)
    {
        $parent = OauthToken::find($request->get('access_token'))->society()->first()->id;
        $results = Category::where('type', 'flat_document')
                            ->where('society_id', $parent)
                            ->orderBy('name', 'asc')
                            ->paginate($request->get('per_page'))->toArray();
        
        return $this->presentor->make200Response('Successfully loaded.', $results);
    }

    public function create()
    {
            //
    }

    public function adminTypeList(Request $request)
    {  
        $type = $request->get('type');
//        print_r($type);exit;
        $parent = OauthToken::find($request->get('access_token'))->society()->first()->id;
        if($type)
        {
        $results = Category::where('type', $type)
                    ->where('society_id', $parent)
                    ->orderBy('name', 'asc')
                    ->paginate($request->get('per_page'))->toArray();
        return $this->presentor->make200Response('Successfully loaded.', $results);
         
        }else{
            $results = Category::where('society_id', $parent)
                        ->orderBy('name', 'asc')
                        ->paginate($request->get('per_page'))->toArray();
            return $this->presentor->make200Response('list', $results);
        }
        
    }
    
	
        public function adminTypeLists(Request $request ,$type)
        {  
                $parent = OauthToken::find($request->get('access_token'))->society()->first()->id;
                $results = Category::where('type', $type)
                                        ->where('society_id', $parent)
                                        ->orderBy('name', 'asc')
                                        ->paginate($request->get('per_page'))->toArray();
                return $this->presentor->make200Response('Successfully loaded.', $results);
        }
	
     public function superadminTypeList(Request $request ,$type)
    {    
        $results = Category::where('type', $type)->orderBy('name', 'asc')->paginate($request->get('per_page'))->toArray();
        return $this->presentor->make200Response('Successfully loaded.', $results);
    }
    
    public function editType($id)
    {
        $results = Category::where('id', $id)->first();
        $results->is_mandatory = (int)$results->is_mandatory; 
        return $this->presentor->make200Response('Successfully loaded.', $results);
    }

    public function adminSaveOrUpdateType(Request $request)
    {  
        $attributes = \Input::all();
      
                $validator = \Validator::make(
                    $attributes,
                            array(
                                'name' => 'Required',
                                'type'=>'Required'
                            )
                );

                if ($validator->fails()){
                    return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false];
                }
                
        $parent = OauthToken::find($request->get('access_token'))->society()->first()->id;
        
        if($request->has('id')) 
        {
           
            $updateType = Category::where('name','=',$request->get('name'))
                        ->where('type','=',$request->get('type'))
                        ->where('id','<>',$request->get('id'))
                        ->where('society_id','=',$parent)
                        ->first();
//            print_r($updateType);exit;
            if ($updateType) {
                return $this->presentor->make400Response('Category \''.$request->get('name').'\' already exists.');
            }
            $updateType = Category::find($request->get('id'));
            $updateType->society_id = $parent;
            $updateType->fill($request->all());
            $updateType->save();
            
        }else
        {
            $updateType = Category::where('name','=',$request->get('name'))
                        ->where('type','=',$request->get('type'))
                        ->where('society_id','=',$parent)
                        ->first();
        
            if ($updateType) {
                return $this->presentor->make400Response('Category \''.$request->get('name').'\' already exists.');
            }
            $updateType = new Category();
            $updateType->society_id = $parent;
            $updateType->fill($request->all());
            $updateType->save();
        }
         return $this->presentor->make200Response('Category saved successfully.', $updateType);
//        
    }
    
     public function superadminSaveOrUpdateType(Request $request)
    {  
        if($request->has('id')) 
        {
            $updateType = Category::where('name','=',$request->get('name'))
                        ->where('id','<>',$request->get('id'))
                        ->where('type','=',$request->get('type'))
                        ->first();
        
            if ($updateType) {
                return $this->presentor->make400Response('Type \''.$request->get('name').'\' already exists.');
            }
            $updateType = Category::find($request->get('id'));
            $updateType->fill($request->all());
            $updateType->save();
            
        }else
        {
            $updateType = Category::where('name','=',$request->get('name'))
                        ->where('type','=',$request->get('type'))
                        ->first();
        
            if ($updateType) {
                return $this->presentor->make400Response('Category \''.$request->get('name').'\' already exists.');
            }
            $updateType = new Category();
            $updateType->fill($request->all());
            $updateType->save();
        }
         return $this->presentor->make200Response('Category saved successfully.', $updateType);
//         return ['success'=>true,'msg'=>'Building saved successfully','data'=>$updateSocietyType];
    }
   
    public function deleteType(Request $request ,$id)
    {
       
            $results = "";
               if ($request->get('type') == 'Society'){
                $results = Society::where('society_category_id','=',$id)->count();
                }
                
                elseif ($request->get('type') == 'Society Document'){
                $results = File::where('category_id','=',$id)
                                ->whereNull('deleted_at')
                                ->count();
                
                }
                elseif ($request->get('type') == 'Meeting'){
                $results = Meeting::where('type_id','=',$id)
                                ->whereNull('deleted_at')
                                ->count();
                }
                elseif ($request->get('type') == 'Flat Document'){
                $results = File::where('category_id','=',$id)
                                ->whereNull('deleted_at')
                                ->count();
                }
                elseif ($request->get('type') == 'Task'){
                $results = Task::where('task_category_id','=',$id)                                
                                ->count();
                }
                elseif ($request->get('type') == 'Helpdesk'){
                $results = Ticket::where('category_id','=',$id)                                
                                ->count();
                }
                
//                 elseif ($request->get('type') == 'official_communication'){
//                $official_results = File::where('type_id','=',$id)
//                                ->whereNull('deleted_at')
//                                ->count();
//                }
//                if($official_results > 0) {
//                    return ['msg'=>'Unable to Delete as this Category is assigned.' ,'success'=>false];
//                }
//                if($meeting_results > 0) {
//                    return ['msg'=>'Unable to Delete as this Category is assigned.' ,'success'=>false];
//                }
//                if($file_results > 0) {
//                    return ['msg'=>'Unable to Delete as this Category is assigned.' ,'success'=>false];
//                }
                
                if($results > 0) {
                    return ['msg'=>'Unable to Delete as this Category is assigned.' ,'success'=>false];
                }

                    $type = Category::find($id);                   
                    $type->delete();
                    return ['msg'=>'Category deleted successfully','success'=>true];
    }
		
//        Category::find($id)->delete();
//
////            return ['msg'=>'Block deleted successfully','success'=>true];
//            return $this->presentor->make200Response('Type deleted successfully.');
    
    
//     public function admindeleteType($id)
//    {
//        Category::find($id)->delete();
//
//            return $this->presentor->make200Response('Type deleted successfully.');
//    }

    public function societyCount(){

        //$results = \DB::selectOne('select count(category.id) as count from category where type = \'society\'');

        $results = Category::whereType('society')->count();
        return $this->presentor->make200Response('Building saved successfully.', $results);


    }
    
    public function SuperadminCheckDuplicateType(Request $request){
            
        $duplicateType = Category::where('name','=',$request->get('name'))
                        ->where('type','=',$request->get('type'))
                        ->first();
        
            if ($duplicateType) {
                return $this->presentor->make400Response('Category \''.$request->get('name').'\' already exists.');
                
            }
            else{
                return $this->presentor->make200Response('no duplicate found');
            } 
    }
    
    public function adminCheckDuplicateType(Request $request){
        $type  = $request->get('type');
        $parent = OauthToken::find($request->get('access_token'))->society()->first()->id;  
//        
        $duplicateType = Category::where('name','=',$request->get('name'))
                        ->where('type','=',$request->get('type'))
                        ->where('society_id','=',$parent)
                        ->first();
//        print_r($duplicateType);exit;
            if ($duplicateType) {
//                print_r($type);exit;
                return $this->presentor->make400Response('category \''.$request->get('name').'\' already exists in '.$type.' type.');
                
            }
            else{
                return $this->presentor->make200Response('no duplicate found',$type);
            } 
    }
    
    // return category list with respect to its type    
    public function CategoryListWithType(Request $request,$type)
  {
        $society_id = OauthToken::find($request->get('access_token'))->society()->first()->id;
         $results = Category::where('type', $type)
                    ->where('society_id', $society_id)->get();
         return $this->presentor->make200Response('Successfully loaded.', $results);
    }
}
