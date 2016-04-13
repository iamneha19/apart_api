<?php

namespace ApartmentApi\Commands\Import;

use ApartmentApi\Exceptions\InvalidBuildingDataException;
use ApartmentApi\Exceptions\InvalidBuildingParkingException;
use ApartmentApi\Exceptions\InvalidFieldException;
use ApartmentApi\Exceptions\InvalidSocietyAmenitiesException;
use ApartmentApi\Exceptions\InvalidSocietyParkingException;
use ApartmentApi\Models\Amenity;
use ApartmentApi\Models\ParkingCategory;
use ApartmentApi\Repositories\SocietyConfigValidationRepository;
use Api\Commands\SelectCommand;
use Illuminate\Bus\Dispatcher;
use Maatwebsite\Excel\Excel;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;


class GetValidSocietyConfig extends SelectCommand
{
    protected $rules = [
        'config_file' => 'required',
        'society_id'  => 'required'
    ];

    protected $buildings;
	
	protected $societyAmenities;
	
	protected $societyConfigData;
	
	protected $importData;
	
	protected $resultData;

	protected $errorBag = [];
	
	protected $hasException = false;

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(Excel $excel, SocietyConfigValidationRepository $repo, Dispatcher $dispatcher)
	{
		if (is_null($file = $this->request->file('config_file'))) {
			throw new FileNotFoundException("Society config file not found.");
        }
			$sheetList = ['society amenities', 'building configuration', 'society parking', 'building parking', 'worksheet','wing amenities','building amenities'];
			$sheets = $excel->load($file)->get();
			foreach ($sheets as $sheet) {
				
				if (!in_array(strtolower($sheet->getTitle()), $sheetList)) {
					array_push($this->errorBag, "Please enter proper sheet name" );
				}
					
				if (strtolower($sheet->getTitle()) === "society amenities"){
						try{
							$dispatcher->dispatch(new ValidateSocietyAmenites($sheet->toArray(), $sheet->getTitle()));
							$this->societyConfigData['SocietyAmenities'] = $sheet->toArray();
						} catch (InvalidSocietyAmenitiesException $ex) {
							$this->hasException = true;
							array_push($this->errorBag, $ex->getErrorBag());
						}	
				}
				
				else if (strtolower($sheet->getTitle()) === "building configuration") {
						try{
							$dispatcher->dispatch(new ValidateBuildings($sheet->toArray(), $sheet->getTitle()));
							$this->societyConfigData['BuildingConfiguration'] = $sheet->toArray(); 
						} catch (InvalidBuildingDataException $ex) {
							$this->hasException = true;
							array_push($this->errorBag, $ex->getErrorBag());
						}
				}	
				else if (strtolower($sheet->getTitle()) === "society parking") {
						try{
							$dispatcher->dispatch(new ValidateSocietyParking($sheet->toArray(), $sheet->getTitle()));
							$this->societyConfigData['SocietyParking'] = $sheet->toArray(); 
						} catch (InvalidSocietyParkingException $ex) {
							$this->hasException = true;
							array_push($this->errorBag, $ex->getErrorBag());
						}
				}		
				else if (strtolower($sheet->getTitle()) === "building parking") {
					try{
							$dispatcher->dispatch(new ValidateBuildingParking($sheet->toArray(), $sheet->getTitle()));
							$this->societyConfigData['BuildingParking'] = $sheet->toArray(); 
					} catch (InvalidBuildingParkingException $ex) {
						$this->hasException = true;
						array_push($this->errorBag, $ex->getErrorBag());
					}
				}else if (strtolower($sheet->getTitle()) === "wing amenities"){
					try{
						
							$dispatcher->dispatch(new ValidateWingAmenities($sheet->toArray(), $sheet->getTitle()));
							$this->societyConfigData['WingAmenities'] = $sheet->toArray(); 
					}catch (InvalidBuildingAmentiesException $ex) {
						$this->hasException = true;
						array_push($this->errorBag, $ex->getErrorBag());
					}
				}
				else if (strtolower($sheet->getTitle()) === "building amenities"){
					try{
							$dispatcher->dispatch(new ValidateBuildingAmenities($sheet->toArray(), $sheet->getTitle()));
							$this->societyConfigData['BuildingAmenities'] = $sheet->toArray(); 
					} catch (InvalidBuildingParkingException $ex) {
						$this->hasException = true;
						array_push($this->errorBag, $ex->getErrorBag());
					}
				}			
							
			}
			
			if ($this->hasException)
				throw new InvalidFieldException("Error occured during import.", array_flatten ($this->errorBag));
			return $this->runFilters();
	}

	protected function runFilters()
    {
		$this->filterAmenities();
		$this->filterWingAmenities();
		$this->filterbuildingAmenities();
		$this->filterParking();
        return $this->societyConfigData;
    }
	
	protected function filterParking()
    {
		$societyParkingData = collect();
		foreach ($this->societyConfigData['SocietyParking']  as $key => $parking) {
			$parking['parking_category_id'] = $this->getValidParkingId($parking['parking_category'])[0];
			$societyParkingData->push($parking);
		
		}
		$this->societyConfigData['SocietyParking'] = $societyParkingData;
		
		$buildingParkingData = collect();
		foreach ($this->societyConfigData['BuildingParking']  as $key => $parking) {
			$parking['parking_category_id'] = $this->getValidParkingId($parking['parking_category'])[0];
			$buildingParkingData->push($parking);
		
		}
		$this->societyConfigData['BuildingParking'] = $buildingParkingData;
		
	}
	
    protected function filterAmenities()
    {
		$societyAmenityData = collect();
		$societyAmenityConfigData = collect();
		
		foreach ($this->societyConfigData['SocietyAmenities'] as $index => $societyAmenity) {
			$societyAmenityData->push($societyAmenity['society_amenities']);
			$amenity = Amenity::where('name', $societyAmenity['society_amenities'])->first();
			
			$societyAmenity['society_amenity_id'] = $amenity->id;
			$societyAmenityConfigData->push($societyAmenity);
		}
		
		unset($this->societyConfigData['SocietyAmenities']);
		$this->societyConfigData['SocietyAmenities'] = $societyAmenityConfigData;
		// Society Amenities :
		$this->societyConfigData['SocietyAmenities']['society_amenities_id'] =
                        $this->injectSocietyId(
							 $this->getValidAmenitiesId($societyAmenityData->toArray())
                        );
    }
    
    
    protected function filterbuildingAmenities()
    {
    	$societyAmenityData = collect();
    	$societyAmenityConfigData = collect();
    
    	foreach ($this->societyConfigData['BuildingAmenities'] as $index => $societyAmenity) 
    	{
    		$societyAmenityData->push($societyAmenity['building_amenities']);
    		$amenity = Amenity::where('name', $societyAmenity['building_amenities'])->first();
    		$societyAmenity['building_amenity_id'] = $amenity->id;
    		$societyAmenityConfigData->push($societyAmenity);
    	}
    	unset($this->societyConfigData['BuildingAmenities']);
    	
    	$this->societyConfigData['BuildingAmenities'] = $societyAmenityConfigData;
    	// Society Amenities :
    	$this->societyConfigData['BuildingAmenities']['building_amenities_id'] =
    	$this->injectSocietyId(
    			$this->getValidAmenitiesId($societyAmenityData->toArray())
    	);
    }
    
    protected function filterWingAmenities()
    {
    	$WingAmenityData = collect();
    	$WingAmenityConfigData = collect();
    	foreach ($this->societyConfigData['WingAmenities'] as $index => $WingAmenities) 
    	{
    		$WingAmenityData->push($WingAmenities['wing_amenities']);
    		$amenity = Amenity::where('name', $WingAmenities['wing_amenities'])->first();
    		$WingAmenities['wing_amenity_id'] = $amenity->id;
    		$WingAmenityConfigData->push($WingAmenities);
    	}
    
    	unset($this->societyConfigData['WingAmenities']);
    	$this->societyConfigData['WingAmenities'] = $WingAmenityConfigData;
    	// Society Amenities :
    	$this->societyConfigData['WingAmenities']['wing_amenity_id'] =
    	$this->injectSocietyId(
    			$this->getValidAmenitiesId($WingAmenityData->toArray())
    	);
    	// Building Amenities :
    }

    public function getValidAmenitiesId($amenitiesName)
    {
        return Amenity::whereIn('name', $amenitiesName)->lists('id');
    }
	
	public function getValidParkingId($parkingCategory)
    {
		return ParkingCategory::where('category_name', $parkingCategory)->lists('id');
    }

    public function injectSocietyId(array $array)
    {
        $filteredArray = [];

        foreach ($array as $key => $value) {
            $filteredArray[$value] = [
                'society_id' => $this->get('society_id')
            ];
        }

        return $filteredArray;
    }

    protected function amenityToArray($amenityString)
    {
        return explode(',', str_replace(', ', ',', $amenityString));
    }

}
