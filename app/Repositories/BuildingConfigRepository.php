<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\BuildingConfig;
use Repository\Repository;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\BuildingFloorConfig;
class BuildinConfigRepository extends Repository {
    
    protected $model;
    
    public function __construct(BuildingConfig $model)
    {
        $this->model = $model;
        
    }
    
    public  function storeBuilding($request) {        
        $sameFlats = $request->get('is_flat_same_on_each_floor'); 
        $parent = OauthToken::find($request->get('access_token'))->society()->first()->id;
        if($request->has('id')) 
        {
//            update statement for "eql no of flats in each floor" done
            $saveBuildingConfig = BuildingConfig::find($request->get('id'));
            $saveBuildingConfig->building_id = $parent;//not a building_id its a society id from oauthtoken
            $saveBuildingConfig->fill($request->all());
            $saveBuildingConfig->save();
            $configurationId = $saveBuildingConfig->id;
//             update statement for 'same no. of flats on floors' is completed
                 
//            update statement for different no. of flats on floors in progress
            if($sameFlats == 'NO') {
                $flatsOnFloor = $request->get('flats');
                foreach($flatsOnFloor as $floor => $numberOfFloor) {
                    $saveBuildingConfig = BuildingFloorConfig::find($request->get('id'));
                    $saveBuildingConfig->building_configuration_id = $configurationId; 
                    $saveBuildingConfig->floor_no = $floor + 1;
                    $saveBuildingConfig->no_of_flat = $numberOfFloor;
                    $saveBuildingConfig->save();
                }
            }
//            update statement for 'different no. of flats on floors' not completed
        }
        else {
            $saveBuildingConfig = new BuildingConfig();
            $saveBuildingConfig->building_id = $parent;       //not a building_id its a society id from oauthtoken,contact mudasir for building_id    
            $saveBuildingConfig->fill($request->all());
            $saveBuildingConfig->save();       
            $configurationId = $saveBuildingConfig->id;  // last inserted it for building_config_id in building_floor config
            
            if($sameFlats == 'NO') {
                $flatsOnFloor = $request->get('flats');
                foreach($flatsOnFloor as $floor => $numberOfFloor) {
                    $saveBuildingConfig = new BuildingFloorConfig($request->all()); 
                    $saveBuildingConfig->building_configuration_id = $configurationId;
                    $saveBuildingConfig->floor_no = $floor + 1;
                    $saveBuildingConfig->no_of_flat = $numberOfFloor;
                    $saveBuildingConfig->save();
                }
            }
        }
       
    }
     
    public function showBuildingConfig($request) {
        
        $building_id = $request->get('building_id');
        $building = BuildingConfig::where('building_id', $building_id)->first();
        $configId = $building->id;       
        if($building->is_flat_same_on_each_floor =='NO') {
            $building = BuildingFloorConfig::join('building_configuration', 'building_config_floor_info.building_configuration_id', '=', 'building_configuration.id')                                       
                                        ->select('building_configuration.id','building_configuration.building_id','building_configuration.no_of_floor',
                                                'building_configuration.is_flat_same_on_each_floor','building_config_floor_info.building_configuration_id',
                                                'building_config_floor_info.floor_no','building_config_floor_info.id','building_config_floor_info.no_of_flat')
                                        ->where('building_config_floor_info.building_configuration_id', $configId)
                                        ->get();
        }
        return ['results'=>$building];
                            
    }
}

