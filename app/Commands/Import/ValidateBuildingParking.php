<?php 

namespace ApartmentApi\Commands\Import;

use ApartmentApi\Exceptions\InvalidBuildingParkingException;
use ApartmentApi\Models\ParkingCategory;
use Api\Commands\SelectCommand;
use Illuminate\Support\Facades\Validator;

/*
 *	Validates Building Parking. 
 * 
 *	@category	Society Config Import
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 */

class ValidateBuildingParking extends SelectCommand
{
	protected $buildingParking;
	
	private $rules = [
		'building_name'		=> 'required',
		'parking_category'	=> 'required',
		'initial'			=> 'required',
		'slots'				=> 'required|numeric',
		'row'				=> 'required_if:amenties_parking,Multiple',
		'column'			=> 'required_if:amenties_parking,Multiple,Single',
	];
	
	private $exceptions = [
		'required'		=> 'required_:attribute',
		'numeric'		=> 'numeric_:attribute',
		'required_if'	=> 'required_if_:attribute',
		
	];
	
	private $customMessages = [
		'required_building name'	=> 'Building Name is empty at row ',
		'required_parking category'	=> 'Parking Category is empty at row ',
		'required_initial'			=> 'Initial is empty at row ',
		'required_slots'			=> 'Slot is empty at row ',
		'required_if_row'			=> 'Row is empty at row ',
		'required_if_column'		=> 'Column Type is empty at row ',
		'numeric_slots'				=> 'Slot is invalid at row ',
		'numeric_row'				=> 'Row is invalid at row ',
		'numeric_column'			=> 'Column is invalid at row ',
		'invalid_single_slots'	    => 'Slot is not equal with column at row ',
		'invalid_multiple_slots'    => 'Slot is not equal with column at row ',
		'invalid_parking category'  => 'Parking Category is invalid at row ',
	];
	
	protected $isCustomMessageSet = [
		'required_building name'	=> false,
		'required_amenties parking'	=> false,
		'required_initial'			=> false,
		'required_slots'			=> false,
		'required_if_row'			=> false,
		'required_if_column'		=> false,
		'numeric_slots'				=> false,
		'numeric_row'				=> false,
		'numeric_column'			=> false,
		'invalid_single_slots'		=> false,
		'invalid_multiple_slots'	=> false,
		'invalid_parking category'  => false,
	];

	protected $sheetName ;
	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($buildingParking, $sheet)
	{
		$this->buildingParking = $buildingParking;
		$this->sheetName = $sheet;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{		
		foreach ($this->buildingParking as $index => $buildingParking) {
			$this->isSlotSame($buildingParking, $index);
			
			$validator = Validator::make($buildingParking, $this->rules, $this->exceptions);
			if ($validator->fails()) {
				foreach (array_keys($this->rules) as $rule) {
					if ($messageKey = $validator->messages()->first($rule)) {
						$this->isCustomMessageSet[$messageKey] = true;
						$this->customMessages[$messageKey] .= ($index + 2) . ',';
					}
				}
			}
			else {
				$parkingCategoryId = ParkingCategory::where('category_name', $buildingParking['parking_category'])->lists('id');
				if (empty($parkingCategoryId)){
					$this->isCustomMessageSet['invalid_parking category'] = true;
					$this->customMessages['invalid_parking category'] .= ($index + 2) . ',';
				}
			}
			
		}
		//dd($this->customMessages);
		if (in_array(true, $this->isCustomMessageSet))
			throw new InvalidBuildingParkingException("Error occured during import.",  $this->getCustomMessages());
	}
	
	protected function isSlotSame($societyParking, $index)
	{
		if (strtolower($societyParking['parking_category']) === 'multiple' and 
			$societyParking['row'] * $societyParking['column'] !== $societyParking['slots']){
				$this->isCustomMessageSet['invalid_multiple_slots'] = true;
				$this->customMessages['invalid_multiple_slots'] = $this->customMessages['invalid_multiple_slots'] . ($index + 2) ;
		}
		
		if (strtolower($societyParking['parking_category']) === 'single' and 
			$societyParking['column'] !== $societyParking['slots']) {
				$this->isCustomMessageSet['invalid_single_slots'] = true;
				$this->customMessages['invalid_single_slots'] = $this->customMessages['invalid_single_slots'] . ($index + 2) ;
		}
		
	}
	
	protected function getCustomMessages()
	{
		$bag = [];
		
		foreach ($this->isCustomMessageSet as $messageKey => $isSet) {
			if ($isSet) {
				$bag[] = rtrim($this->customMessages[$messageKey], ", ") . " in Sheet " . $this->sheetName;
			}
		}
		return $bag;
	}
	
}
