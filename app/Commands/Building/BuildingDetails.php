<?php
namespace ApartmentApi\Commands\Building;

use Illuminate\Contracts\Bus\SelfHandling;
use DB;
use ApartmentApi\Models\Society;
use ApartmentApi\Models\SocietyConfig;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MeetingReminder
 *
 * @author neha.agrawal
 */
class BuildingDetails implements SelfHandling  {
    //put your code here
    protected $societyId;
    
    function __construct($societyId) 
    {
        $this->societyId = $societyId;
    }
    function handle()
    {
        $results = $this->getBuildingDetails();
        return $results;
    }
    
    public function getBuildingDetails()
    {
       $building_count = Society::where('parent_id','=',$this->societyId)->count();
        
//        $amenities = \DB::select("SELECT group_concat(DISTINCT amenity.name) as amenity_name FROM amenity_tags
//                INNER JOIN amenity ON amenity.id = amenity_tags.amenity_id
//                WHERE amenity_tags.society_id = '".$this->societyId."' GROUP BY amenity_tags.society_id ");
       $amenities = \DB::select("SELECT amenity.id,amenity.name AS amenity,GROUP_CONCAT(sub_amenities.name) AS sub_amenities FROM amenity_tags
                                INNER JOIN amenity ON amenity.id = amenity_tags.amenity_id
                                LEFT JOIN sub_amenities ON amenity_tags.sub_amenity_id = sub_amenities.id
                                WHERE amenity_tags.society_id = '".$this->societyId."' AND amenity_tags.taggable_id = '".$this->societyId."'
                                GROUP BY amenity.id");
       
        $building_details = \DB::select("SELECT society.id AS building_id,society.parent_id AS society_id,society.name,society.wing_exists,amenity_tags.id,GROUP_CONCAT(amenity.name) AS amenity_name FROM society
                                    LEFT JOIN amenity_tags ON amenity_tags.taggable_id = society.id
                                    LEFT JOIN amenity ON amenity.id = amenity_tags.amenity_id
                                    WHERE parent_id = '".$this->societyId."' GROUP BY society.id");
//        print_r($building_details);exit;
        
        $society_config = SocietyConfig::where('society_id','=',$this->societyId)->get();
        return ['amenities' => $amenities,'count'=>$building_count,'building_details'=>$building_details,'society_config'=>$society_config];
    }
}
