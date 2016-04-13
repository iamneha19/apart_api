<?php

namespace ApartmentApi\Repositories;

use Repository\Repository;

 /**
  * Society Config Repository
  *
  * @author Swapnil Chaudhari
  */
class SocietyConfigValidationRepository extends Repository
{
    protected $buildings;
	
	protected $sampleColumns = array('building_name', 'wing_name', 'flat_numbers', 'floor', 'sq_ft', 'flat_type', 'wing_amenities', 'building_amenities', '0', '1');
	
	protected $sampleFlatTypes = array('flat','office','shop');
	
	protected $rowCounter = 1;

	protected $emptyBuildingRows = array();
	
	protected $emptyFlatsRows = array();
	
	protected $emptyFloorsRows = array();
	
	protected $emptySqFtsRows = array();
	
	protected $emptyFlatTypeRows = array();
	
	protected $invalidFlatTypeRows = array();
	
	protected $invalidFloorsRows = array();
	
	protected $invalidSqFtRows = array();
	
	protected $invalidFlatsRows = array();
	
	protected $errorResult = 0;
	
	protected $errorBag = [
		'FileNotFoundException'			=> '',
		'ColumnMismatchException'		=> '',
		'EmptyFieldException'			=> [
			'BuildingException'		=> '',
			'FlatException'				=> '',
			'FloorException'			=> '',
			'SqFtException'				=> '',
			'FlatTypeException'			=> '',
		],
		'InvalidFlatTypeException'		=> '',
		'InvalidSqFtException'			=> '',
		'InvalidFloorException'			=> '',
		'InvalidFlatNumbersException'	=> '',
		'BuildingAmenititesException'	=> '',
		'WingAmenitiessException'		=> '',
	];

    public function validateBuildingData($buildings)
    {
		$this->buildings = $buildings;
		$firstRecord = $this->buildings[0][0];
		$societyConfigColumns = array_keys($firstRecord);
		
		foreach ($societyConfigColumns as $key => $value) {
			if (! in_array($value, $this->sampleColumns))
				$this->errorBag['FileNotFoundException'] = 'Please upload configuration file with proper column name.';
		}
		
		foreach ($this->buildings as $key1 => $value1) {
			foreach ($value1 as $key => $value) {
				$this->rowCounter++ ;
				$this->validateEmptyFields($value);
//				$this->validateBuildingAmenities($value);
//				$this->validateWingAmenities($value);

			}
		}
		
		$this->generateErrorBag();
		
		if ($this->errorResult === 0){
			$this->errorBag['errorStatus'] = 0;
			return $this->buildings;
		}
		else {
			$this->errorBag['errorStatus'] = 1;
			return $this->errorBag;
		}
			
		
	}
	
	protected function generateErrorBag()
    {
		if (count($this->emptyBuildingRows) > 0){
			$this->errorBag['EmptyFieldException']['BuildingException'] = 'There is Building Column missing in row '. implode (",", $this->emptyBuildingRows);
			$this->errorResult = 1;
		}
		
		if (count($this->emptyFlatsRows) > 0) {
			$this->errorBag['EmptyFieldException']['FlatException'] = 'There is Flat Number Column missing in row '. implode (",", $this->emptyFlatsRows);
			$this->errorResult = 1;
		}
		
		if (count($this->emptyFloorsRows) > 0) {
			$this->errorBag['EmptyFieldException']['FloorException'] = 'There is Floor Column missing in row '. implode (",", $this->emptyFloorsRows);
			$this->errorResult = 1;
		}
		
		if (count($this->emptySqFtsRows) > 0) {
			$this->errorBag['EmptyFieldException']['SqFtException'] = 'There is Sq Ft Column missing in row '. implode (",", $this->emptySqFtsRows);
			$this->errorResult = 1;
		}
		
		if (count($this->emptyFlatTypeRows) > 0) {
			$this->errorBag['EmptyFieldException']['FlatTypeException'] = 'There is Flat Type Column missing in row '. implode (",", $this->emptyFlatTypeRows);
			$this->errorResult = 1;
		}
		
		if (count($this->invalidFlatTypeRows) > 0) {
			$this->errorBag['InvalidFlatTypeException'] = 'Invalid Flat Type in row '. implode(",", $this->invalidFlatTypeRows);
			$this->errorResult = 1;
		}
		
		if (count($this->invalidFlatsRows) > 0) {
			$this->errorBag['InvalidFlatNumbersException'] = 'Invalid Flat Number in row '. implode(",", $this->invalidFlatsRows);
			$this->errorResult = 1;
		}
		
		if (count($this->invalidSqFtRows) > 0) {
			$this->errorBag['InvalidSqFtException'] = 'Invalid Sq Ft in row '. implode(",", $this->invalidSqFtRows);
			$this->errorResult = 1;
		}
		
		if (count($this->invalidFloorsRows) > 0) {
			$this->errorBag['InvalidFloorException'] = 'Invalid Floor in row '. implode(",", $this->invalidFloorsRows);
			$this->errorResult = 1;
		}
	}
	
	protected function validateEmptyFields($buildingData) {
		if (empty($buildingData['building_name'])) {	
			array_push($this->emptyBuildingRows, $this->rowCounter);
		}
		
		if (empty($buildingData['flat_numbers'])) {	
			array_push($this->emptyFlatsRows, $this->rowCounter);
		}
		
		if (empty($buildingData['floor'])) {
			array_push($this->emptyFloorsRows, $this->rowCounter);
		}
		
		if (empty($buildingData['sq_ft'])) {
			array_push($this->emptySqFtsRows, $this->rowCounter);
		}
		
		if (empty($buildingData['flat_type'])) {
			array_push($this->emptyFlatTypeRows, $this->rowCounter);
		}
		
		$this->validateFlatType($buildingData);
		
	}
	
	protected function validateFlatType($buildingData) {
		if (!in_array(strtolower($buildingData['flat_type']), $this->sampleFlatTypes))	
			array_push($this->invalidFlatTypeRows, $this->rowCounter);
		
		$this->validateNumericFields($buildingData);
	}
	
	protected function validateNumericFields($buildingData) {
		if (! is_numeric($buildingData['flat_numbers']))	
			array_push($this->invalidFlatsRows, $this->rowCounter);
				
		if (! is_numeric($buildingData['sq_ft']))	
			array_push($this->invalidSqFtRows, $this->rowCounter);
				
		if (! is_numeric($buildingData['floor']))	
			array_push($this->invalidFloorsRows, $this->rowCounter);
			
		$this->validateBuildingAmenities($buildingData);
	}
    
	protected function validateBuildingAmenities($buildingData) {
		$buildingAmenitiesData = array(
										'buildingName'			=>	'',
										'buildingAmenities'		=>	'',
									  );

		if (!in_array($buildingData['building_name'], $buildingAmenitiesData)){
			$buildingAmenitiesData['buildingName']		= $buildingData['building_name'];
			$buildingAmenitiesData['buildingAmenities'] = $buildingData['building_amenities'];
		}

		if ($buildingData['building_name'] === $buildingAmenitiesData['buildingName'] and 
			$buildingData['building_amenities'] !== $buildingAmenitiesData['buildingAmenities'] and
			empty($this->errorBag['BuildingAmenititesException'])){

			$this->errorBag['BuildingAmenititesException'] = 'Building Amenities must be same for all flats under that building.';	
		}
		
		$this->validateWingAmenities($buildingData);
	}
	
	protected function validateWingAmenities($wingData) {
		$wingAmenitiesData = array(
										'buildingName'			=>	'',
										'wingName'			=>	'',
										'wingAmenities'		=>	'',
									  );
		
		if (!in_array($wingData['building_name'], $wingAmenitiesData) and
			!in_array($wingData['wing_name'], $wingAmenitiesData)){
					
			$wingAmenitiesData['buildingName']		= $wingData['building_name'];
			$wingAmenitiesData['wingName']		= $wingData['wing_name'];
			$wingAmenitiesData['wingAmenities'] = $wingData['wing_amenities'];
		}
				
		if ($wingData['building_name'] === $wingAmenitiesData['buildingName'] and 
			$wingData['wing_name'] === $wingAmenitiesData['wingName'] and 	
			$wingData['wing_amenities'] !== $wingAmenitiesData['wingAmenities'] and
			empty($this->errorBag['WingAmenitiessException'])){

			$this->errorBag['WingAmenitiessException'] = 'Wing Amenities must be same for all flats under that wing.';	
		}
	}
}
