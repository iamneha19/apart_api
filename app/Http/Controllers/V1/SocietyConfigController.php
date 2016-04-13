<?php

namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Commands\Building\BuildingDetails;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\BuildingConfig;
use ApartmentApi\Models\BuildingFloorConfig;
use ApartmentApi\Models\Block;
use ApartmentApi\Models\BlockConfiguration;
use ApartmentApi\Models\BlockConfigurationFloorInfo;
use ApartmentApi\Models\SocietyConfig;
use ApartmentApi\Models\User;
use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\AclUserRole;
use ApartmentApi\Models\UserSociety;
use ApartmentApi\Models\Flat;
use Carbon\Carbon;
use ApartmentApi\Http\Requests;
use ApartmentApi\Http\Middleware\SocietyIdRequest;
use ApartmentApi\Commands\Society\GetSocietyConfig;
use ApartmentApi\Commands\Society\SaveSocietyConfig;
use ApartmentApi\Commands\Society\AddDummyBuilding;
use ApartmentApi\Models\Society;
use ApartmentApi\Models\ParkingCategory;
use ApartmentApi\Models\ParkingConfig;

use Illuminate\Http\Request;
use Api\Controllers\ApiController;

class SocietyConfigController extends ApiController
{
    protected $input;

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(SocietyIdRequest $request)
	{
		return $this->dispatch(new GetSocietyConfig($request));
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
	public function store(SocietyIdRequest $request)
	{
		$job = new SaveSocietyConfig($request);
        return $this->dispatch($job);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
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
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

    public function addDummyBuilding($count, SocietyIdRequest $request)
    {
        $response = $this->dispatch(new AddDummyBuilding($count, $request));

        return $response ? $this->presentor()->make200Response('Successfully created dummy buildings.'):
                        $this->presentor()->make500Response('Unable to create dummy buildings.');
    }

    public function listBuildingDetails(Request $request)
    {
        $society_id = OauthToken::find($request->get('access_token'))->society_id;
        $building_details = new BuildingDetails($society_id);
        $results = $this->dispatch($building_details);
        return $this->presentor->make200Response('Successfully loaded.', $results);
    }

    public function BuildingConfigurationDetails(Request $request)
    {
        $building_id = $request->get('building_id');
        $building_details = BuildingConfig::where('building_id','=',$building_id)
                            ->select('building_configuration.*','building_config_floor_info.*')
                            ->leftJoin('building_config_floor_info','building_configuration.id','=','building_config_floor_info.building_configuration_id')
                            ->get();
       return ['success'=>true,'details'=>$building_details];

    }

    public function getBuildingWings(Request $request,$building_id)
    {
        $wings = Block::where('society_id','=',$building_id)->get();
        return ['wings'=>$wings];
    }

    public function getWingConfigurationDetails(Request $request,$wing_id)
    {
        $wing_details = BlockConfiguration::where('block_id','=',$wing_id)
                        ->select('block_configuration.*','block_config_floor_info.*')
                        ->leftJoin('block_config_floor_info','block_configuration.id','=','block_config_floor_info.block_configuration_id')
                        ->get();
        return ['success'=>true,'details'=>$wing_details];
    }

    public function getBuildingApprovedStatus(Request $request)
    {
        $society_id = OauthToken::find($request->get('access_token'))->society_id;
        $society_name = OauthToken::find($request->get('access_token'))->society()->first();
        $user_details =  OauthToken::find($request->get('access_token'))->user()->first();
        $attributes = $request->all();
        $society_config = SocietyConfig::where('society_id','=',$society_id)->firstOrFail();
        $society_config->fill($attributes);
        $society_config->save();
        $society_admin = AclRole::where('role_name','=','Admin')
                        ->where('society_id','=',$society_id)
                        ->select('acl_role.role_name','acl_user_role.*','users.first_name','users.last_name','users.email')
                        ->join('acl_user_role','acl_role.id','=','acl_user_role.acl_role_id')
                        ->join('users','acl_user_role.user_id','=','users.id')
                        ->firstOrFail();
        $data = array(
                        'description'=>$society_config->notes,
                        'society_name'=>$society_name->name,
                        'from_name'=>$user_details->first_name.' '.$user_details->last_name,
                        'from_email'=>$user_details->email,
                        'to_email'=>$society_admin->email,
                        'to_name'=>$society_admin->first_name.' '.$society_admin->last_name,
                        'status'=>$society_config->is_approved,
                    );
        event(new \ApartmentApi\Events\BuildingApprovedStatus($data));
        return['msg'=>'status updated successfully','success'=>true];
    }
    
    public function getAllBuildingFlats(Request $request,$building_id)
    {
        $society_id = OauthToken::find($request->get('access_token'))->society_id;
        $building_name = Society::where('id','=',$building_id)->select('name')->first();
        $flats = UserSociety::where('building_id','=',$building_id)
                ->select('user_society.flat_id','flat.flat_no','flat.type','flat.floor','flat.square_feet_1')
                ->join('flat','user_society.flat_id','=','flat.id')
                ->get();
       return ['success'=>true,'building_name'=>$building_name,'flats'=>$flats];
        
    }
    
    public function getAllWingFlats(Request $request,$wing_id)
    {
        $wing_name = Block::where('block.id','=',$wing_id)
                ->select('block.id','block.block','block.society_id','society.name')
                ->join('society','block.society_id','=','society.id')
                ->first();
        $flats = Flat::where('block_id','=',$wing_id)->get();
        return ['success'=>true,'wing_name'=>$wing_name,'flats'=>$flats];
    }
    
    public function getBuildingAmenities(Request $request,$building_id)
    {
        $society_id = OauthToken::find($request->get('access_token'))->society_id;
        $building_amenities = \DB::select("SELECT amenity.id,amenity.name AS amenity,GROUP_CONCAT(sub_amenities.name) AS sub_amenities FROM amenity_tags
                                    INNER JOIN amenity ON amenity.id = amenity_tags.amenity_id
                                    LEFT JOIN sub_amenities ON amenity_tags.sub_amenity_id = sub_amenities.id
                                    WHERE amenity_tags.society_id = '".$society_id."' AND amenity_tags.taggable_id = '".$building_id."'
                                    GROUP BY amenity.id");
        return ['success'=>true,'building_amenities'=>$building_amenities];
    }
    
    public function getParking(Request $request,$id)
    {
//        print_r($id);exit;
        $parking = ParkingConfig::where('society_id','=',$id)
                    ->select('parking_config.*','parking_category.*')
                    ->join('parking_category','parking_config.category_id','=','parking_category.id')
                    ->get();
        return ['success'=>true,'parking'=>$parking];
    }
    
}
