<?php
namespace ApartmentApi\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use ApartmentApi\Models\Building;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\Society;
use ApartmentApi\Models\Block;
use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\Flat;
use ApartmentApi\Models\UserSociety;
use ApartmentApi\Models\Amenity;
use ApartmentApi\Models\AmenityTag;
use ApartmentApi\Commands\Building\FindBlock;
use ApartmentApi\Http\Controllers\V1\ApiController;
use ApartmentApi\Http\Requests\BlockConfigurationRequest;
use ApartmentApi\Http\Requests\BlockIdRequest;
use ApartmentApi\Commands\Building\CreateFlats;
use ApartmentApi\Commands\Building\UpdateFlats;
use ApartmentApi\Commands\Building\ListFlats;
use ApartmentApi\Commands\Building\ListBlockAmenities;
                                   

class BuildingController extends ApiController
{

	public function __construct()
	{
		$this->middleware('rest');
	}

	public function createOrUpdate(Request $request) {

		$parent = OauthToken::find($request->get('access_token'))->society()->first();
              
		if ($request->has('id')) {
			$societyId = $parent->id;
			$society1 = society::where('name','=',$request->get('name'))
								->where('parent_id','=',$societyId)
											->where('id','<>',$request->get('id'))
								->first();
		
			if ($society1) {
				return ['msg'=>'Building \''.$request->get('name').'\' already exists.','success'=>false];
			}
                   
			$society = Society::find($request->get('id'));
			$building = Building::find($request->get('id'));
		} else {
			$societyId = $parent->id;
			$society1 = society::where('name','=',$request->get('name'))
			->where('parent_id','=',$societyId)
			->first();
		
			if ($society1) {
				return ['msg'=>'Building \''.$request->get('name').'\' already exists.','success'=>false];
			}
			
			$society = new Society();
			$building = new Building();
		}
                
		$society->parent()->associate($parent);
		$society->fill($request->all());
		$society->save();
		$building->id = $society->id;
		$building->fill($request->all());
		$building->save();
        
        if (!$request->has('id')) { // Create Admin role for building
            $aclRole = new AclRole();
            $aclRole->role_name = 'Admin';
            $aclRole->is_unique = 1;
            $aclRole->is_default = 1;
            $aclRole->society_id = $society->id;
            $aclRole->save();
        }
            

		$data = ['id'=>$society->id,'name'=>$society->name,'floors'=>$building->floors,
				'flats'=>$building->flats,'blocks'=>$building->blocks];

		return ['success'=>true,'msg'=>'Building saved successfully','data'=>$data];
	}
        
        public function checkDuplicate(Request $request) {
            $parent = OauthToken::find($request->get('access_token'))->society()->first();
               
                $societyId = $parent->id;
                $society1 = society::where('name','=',$request->get('name'))
			->where('parent_id','=',$societyId)
			->first();
		
		if ($society1) {
			return ['msg'=> 'Building \''.$request->get('name').'\' already exists.','success'=>false];
		}
                return ['success'=>true];
        }
        
	public function buildings(Request $request) {
		
		$sort = '';
		if (\Input::get('sort',null)){
           $sort =  \Input::get('sort',null);
            if($sort == 'flat'){
                $sort = ' order by society.name '.\Input::get('sort_order','desc').' ';
            }else{
               $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','desc').' ';
            }

        }
		
		if (\Input::get('limit',20))
			$limit = \Input::get('limit',20);
		else
			$limit =10;
		if (\Input::get('offset',0))
			$offset = \Input::get('offset',0);
		else
			$offset = 0;
		$bindings['access_token'] = $request->get('access_token');
		$bindings['limit'] = $limit;
		$bindings['offset'] = $offset;
		$data = \DB::select('
				select society.id,society.name,building.flats,building.floors,building.blocks
				from oauth_token ot
				inner join society on society.parent_id = ot.society_id
				inner join building on building.id = society.id where ot.token = :access_token '.$sort.' limit :limit offset :offset 
				',
				$bindings);
		
		$count = \DB::selectOne('
				select COUNT(society.id) total
				from oauth_token ot
				inner join society on society.parent_id = ot.society_id
				inner join building on building.id = society.id where ot.token = :access_token '.$sort.' limit :limit offset :offset 
				',
				$bindings);
		
		 return array(
                'total'=>$count->total,
                'data'=>$data
            );
	}
	
	public function societyBuildings($societyId, Request $request) {
		
		return \DB::select('
				select society.id,society.name,building.flats,building.floors,building.blocks
				from society inner join building on building.id = society.id 
				where society.parent_id = :society_id
				',

				['society_id'=>$societyId]);
		
	}

	public function building($id,Request $request) {

			$response = [];
		
			$collection = \DB::selectOne('
				select society.id,society.name,building.flats,building.floors,building.blocks
				from oauth_token ot
				inner join society on society.parent_id = ot.society_id
				inner join building on building.id = society.id where ot.token = :access_token
				and society.id = :id
				',

				['access_token'=>$request->get('access_token'),'id'=>$id]);
			
			foreach ($collection as $key => $value)
			{
				$response[$key] = (int) $value === 0 ? $value : (int) $value;
 			}
			
		return $response;

	}

	public function deleteBuilding($id,Request $request) {

             $results = UserSociety::where('building_id','=',$id)->count();	
		if($results > 0){
			return ['msg'=>'This building is assigned to Flats' ,'success'=>false];
		}
		Society::where('id','=',$id)
		->where('parent_id','=',OauthToken::find($request->get('access_token'))->society_id)
		->first()
		->delete();

		return ['msg'=>'Building deleted succesfully','success'=>true];
	}
	
	// Wings Configuration :
	public function editBlock(Request $request) {  
		$command = new FindBlock($request);
		$block = $this->dispatch($command);
		
		return $block ?
            $this->presentor()->make200Response('Successfully loaded block.', $block->toArray()):
            $this->presentor()->makeResponseByCode($command->getError(), $command->getStatusCode());
	}
	
	// Adding flats in wings :
	public function addFlats(BlockConfigurationRequest $request) {  
		return [
				'msg'=>'Flats saved successfully', 
				'success'=>$this->dispatch(new CreateFlats($request))
			   ];
	}
	
	// Updating flats in wings :
	public function updateFlats(BlockConfigurationRequest $request) {  
		return ['msg'=>'Flats updated successfully','success'=>$this->dispatch(new UpdateFlats($request))];
	}
	
	// List flats for wing :
	public function listFlats(BlockIdRequest $blockRequest) { 
		$command = new ListFlats($blockRequest);
		$result = $this->dispatch($command);

		return ($result)  ?
            $this->presentor()->make200Response('Successfully loaded wing.', $result):
            $this->presentor()->makeResponseByCode($command->getError(), 404);
	}
	
	// List Amenities for Block :
	public function listBlockAmenities(BlockIdRequest $blockRequest)
    {
		$command = new ListBlockAmenities($blockRequest);
		$amenities = $this->dispatch($command);
		
		return $amenities ?
            $this->presentor()->make200Response($command->getMessage(), $amenities):
            $this->presentor()->makeResponseByCode($command->getError(), $command->getStatusCode());
	}

	//wings Create and Update :
	public function createOrUpdateBlock(Request $request) {  
		$amenityIdArray = explode(",", $request->get('amenitiesId'));
		
		if ($request->has('blockId')) {
                        $block = Block::where('block','=',$request->get('block'))
			->where('society_id','=',$request->get('building_id'))
                        ->where('id','<>',$request->get('blockId'))
			->first();
			
						
			if ($block) {
			return ['msg'=>'Wing \''.$request->get('block').'\' already exists.','success'=>false];
		}
			$block = Block::find($request->get('blockId'));
			$msg = 'Wing updated successfully';
			
		} else {
                    
			$blocks = Building::find($request->get('building_id'))->blocks;
			$block = Block::where('block','=',$request->get('block'))
							->where('society_id','=',$request->get('building_id'))
							->first();
			if ($block) {
			
				return ['msg'=>'Wing \''.$request->get('block').'\' already exists.','success'=>false];
			}
			$block = Block::find($request->get('id'));
             
//			$count = \DB::selectOne('select count(id) count from block where society_id = :building_id',
//				['building_id'=>$request->get('building_id')]);
//		
//            if ($count->count >= $blocks) {
//				return ['success'=>false,'msg'=>'No. of wings exceeds the wing limit of building'];
//			}
                       
			$block = new Block();
			$society = Society::find($request->get('building_id'));
			$block->society()->associate($society);
			$msg = 'Wing saved successfully';
		}
		
		
		
		DB::transaction(function() use ($amenityIdArray, $request,$block) {
			$block->block = $request->get('block');
			$block->save();
			
			$amenities = Amenity::whereIn('id',$amenityIdArray)->get();
			
			$amenities_tags = AmenityTag::whereTaggableId($block->id)->delete();

			
			foreach ($amenities as $amenity) {
				$block->amenities()->save($amenity, ['society_id' => $request->get('building_id')]);
			}
		});
		
		
		return ['msg'=>$msg,'success'=>true,'data'=>$block];
		
	}
	
        public function checkDuplicateBlock(Request $request) {
            
            $block = Block::where('block','=',$request->get('block'))
			->where('society_id','=',$request->get('building_id'))
			->first();
		
		if ($block) {
			return ['msg'=>'Block \''.$request->get('block').'\' already exists.','success'=>false];
		}
                return ['success'=>true];
            
        }
	
	public function blocks($buildingId, Request $request) {
		return Block::where('society_id','=',$buildingId)
								->get(['id','block']);
	}
	
	public function deleteBlock($blockId) {
		$sql = 'select count(*) as total from flat where block_id = :block_id';
		$result = \DB::selectOne($sql,['block_id'=>$blockId]);
		
		if ($result->total) {
			
			return ['msg'=>'Flats are assigned to this Block','success'=>false];
		} else {
					DB::transaction(function() use ($blockId)  {

						$block = Block::find($blockId);
						$block->delete();	

						$amenities_tags = AmenityTag::whereTaggableId($blockId)->delete();
			
					});
			
					return ['msg'=>'Wing Deleted Successfully','success'=>true];
		}
	}
}
