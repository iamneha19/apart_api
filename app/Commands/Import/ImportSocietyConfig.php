<?php namespace ApartmentApi\Commands\Import;

use ApartmentApi\Commands\Command;
use ApartmentApi\Events\SocietyConfigUploaded;
use ApartmentApi\Models\AmenityTag;
use ApartmentApi\Models\Building;
use ApartmentApi\Models\ParkingConfig;
use ApartmentApi\Models\ParkingSlot;
use ApartmentApi\Models\Society;
use ApartmentApi\Models\SubAmenities;
use ApartmentApi\Models\Block;

use DB;
use Illuminate\Contracts\Bus\SelfHandling;

class ImportSocietyConfig extends Command implements SelfHandling
{
    protected $societyConfigData;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($societyId, $societyConfigData)
	{
        $this->societyId = $societyId;
        $this->societyConfigData = $societyConfigData;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(Society $society)
	{
		
		return DB::transaction(function() use ($society) {
			
			$this->importSocietyAmenitiesData($society);
			
			$this->importWingAmenitiesData($society);
			
			$this->importBuildingAmenitiesData($society);
				
				
//			
			$this->importSocietyParkingData();
			
			$this->importBuildingData($society);
            
			$this->importBuildingParkingData($society);
			
			event(new SocietyConfigUploaded());

            return true;
        });
	}
	
	public function importBuildingParkingData($society)
	{
		foreach ($this->societyConfigData['BuildingParking'] as $parking) {
			$buildingData = $society->firstOrCreate([
				'parent_id' => $this->societyId,
				'name' => $parking['building_name']
			]);
			// save building parking :
			$buildingParking = ParkingConfig::firstOrCreate([
				'slot_name_prefix'	=> $parking['initial'],
				'stack_row'			=> $parking['row'] ?:0,
				'stack_column'		=> $parking['column']?:0,
				'total_slot'		=> $parking['slots'],
				'society_id'		=> $buildingData->id,
				'category_id'		=> $parking['parking_category_id'],
				'name'				=> $parking['name']?:"",
				'styling'			=> $parking['styling']?:"_",
			]);
			
			// save parking slot :
			for($i = 1; $i <= $parking['slots']; $i++) {
				
				$i1 = sprintf("%0".strlen($parking['slots'])."d", $i);
				$societyParkingSlot = ParkingSlot::firstOrCreate([
					'slot_name'			=>	$parking['initial'] . $parking['styling'] . $i1,
					'category_id'		=>	$parking['parking_category_id'],
					'society_id'		=>	$buildingData->id,
					'parking_config_id'	=>	$buildingParking->id,
				]);
			}
		}
		
		
	}
	
	public function importSocietyParkingData()
	{
		foreach ($this->societyConfigData['SocietyParking'] as $parking) {
			// save society parking :
			$societyParking = ParkingConfig::firstOrCreate([
				'slot_name_prefix'	=> $parking['initial'],
				'stack_row'			=> $parking['row'] ?:0,
				'stack_column'		=> $parking['column']?:0,
				'total_slot'		=> $parking['slots'],
				'society_id'		=> $this->societyId,
				'category_id'		=> $parking['parking_category_id'],
				'name'				=> $parking['name']?:"",
				'styling'			=> $parking['styling']?:"_",
			]);
			
			
			// save parking slot :
			for($i = 1; $i <= $parking['slots']; $i++) {
				$i1 = sprintf("%0".strlen($parking['slots'])."d", $i);
				$societyParkingSlot = ParkingSlot::firstOrCreate([
					'slot_name'			=>	$parking['initial'] . $societyParking['styling'] . $i1,
					'category_id'		=>	$parking['parking_category_id'],
					'society_id'		=>	$this->societyId,
					'parking_config_id'	=>	$societyParking->id,
				]);
			}

        }
	}
	
	public function importSocietyAmenitiesData($society)
	{
		$societyBuilding = $society->where('id', $this->societyId)->first();
		
		foreach ($this->societyConfigData['SocietyAmenities']->toArray() as $key => $societyAmenity) {
			
			if (!empty($societyAmenity['name'])){
				$subAmenityData = SubAmenities::firstOrCreate([
									'society_id'	=>	$societyBuilding->id,
									'name'			=>	$societyAmenity['name'],
									'amenity_id'	=>	$societyAmenity['society_amenity_id'],
								]);
				
				$amenityTag = AmenityTag::firstOrCreate([
					'society_id'		=>	$societyBuilding->id,
					'amenity_id'		=>	$societyAmenity['society_amenity_id'],
					'sub_amenity_id'	=>	$subAmenityData->id,
					'taggable_id'		=>	$societyBuilding->id,
					'taggable_type'		=>	'ApartmentApi\Models\Society',	
				]);
			}
			
					
		}
		
		$societyBuilding->amenities()->sync($this->societyConfigData['SocietyAmenities']['society_amenities_id']);
	}
	
	
	public function importWingAmenitiesData($society)
	{
		$societyBuilding = $society->where('id', $this->societyId)->first();
		
		foreach ($this->societyConfigData['WingAmenities']->toArray() as $key => $societyAmenity) {
			
			if (!empty($societyAmenity['name']))
			{
				$buildingData = $society->firstOrCreate([
						'parent_id' => $this->societyId,
						'name' => $societyAmenity['building_name']
						]);
				$wingData = Block::firstOrCreate([
						'society_id' => $buildingData['id'],
						'block' => $societyAmenity['wing_name']
						]);
				
				$subAmenityData = SubAmenities::firstOrCreate([
						'society_id'	=>	$societyBuilding->id,
						'name'			=>	$societyAmenity['name'],
						'amenity_id'	=>	$societyAmenity['wing_amenity_id'],
						]);
	
				$amenityTag = AmenityTag::firstOrCreate([
						'society_id'		=>	$societyBuilding->id,
						'amenity_id'		=>	$societyAmenity['wing_amenity_id'],
						'sub_amenity_id'	=>	$subAmenityData->id,
						'taggable_id'		=>	$wingData['id'],
						'taggable_type'		=>	'ApartmentApi\Models\Block',
						]);
			}
		}
	
		$societyBuilding->amenities()->sync($this->societyConfigData['SocietyAmenities']['society_amenities_id']);
	}
	
	
	
	public function importBuildingAmenitiesData($society)
	{
		$societyBuilding = $society->where('id', $this->societyId)->first();
	
		foreach ($this->societyConfigData['BuildingAmenities']->toArray() as $key => $societyAmenity) {
				
			if (!empty($societyAmenity['name']))
			{
				$buildingData = $society->firstOrCreate([
							'parent_id' => $this->societyId,
							'name' => $societyAmenity['building_name']
							]);
				
				$subAmenityData = SubAmenities::firstOrCreate([
						'society_id'	=>	$societyBuilding->id,
						'name'			=>	$societyAmenity['name'],
						'amenity_id'	=>	$societyAmenity['building_amenity_id'],
						]);
				$amenityTag = AmenityTag::firstOrCreate([
						'society_id'		=>	$societyBuilding->id,
						'amenity_id'		=>	$societyAmenity['building_amenity_id'],
						'sub_amenity_id'	=>	$subAmenityData->id,
						'taggable_id'		=>	$buildingData['id'],
						'taggable_type'		=>	'ApartmentApi\Models\Society',
						]);
			}
		}
	
		$societyBuilding->amenities()->sync($this->societyConfigData['SocietyAmenities']['society_amenities_id']);
	}
	
	public function importBuildingData($society)
	{
		foreach ($this->societyConfigData['BuildingConfiguration'] as $building) {
                // save Building
                $societyBuilding = $society->firstOrCreate([
                    'parent_id' => $this->societyId,
                    'name' => $building['building_name']
                ]);

                // save wing
                $block = $societyBuilding->blocks()->firstOrCreate([
                    'block' => $building['wing_name']
                ]);

                $flat = $block->flats()
							  ->firstOrCreate([
								'flat_no' => $building['flat_numbers']
							]);
				
				$flat->update([
					'square_feet_1' => $building['sq_ft'],
					'floor' => $building['floor'],
					'type' => strtolower($building['flat_type'])
				]);

                // Required for foreign key violation in society building
                Building::firstOrCreate([
                    'id' => $societyBuilding->id
                ]);

                $userSociety = $societyBuilding->userSociety()->firstOrCreate([
                    'flat_id' => $flat['id'],
                    'block_id' => $block->id,
                    'building_id' => $societyBuilding->id
                ]);
				
				
				
                // Save flat
                // Save building and block amenities
            //    $societyBuilding->amenities()->sync($building['building_amenities_id']);
             //   $block->amenities()->sync($building['wing_amenities_id']);
        	}
	}
}
