<?php 

namespace ApartmentApi\Commands\Import;

use ApartmentApi\Exceptions\InvalidSocietyParkingException;
use ApartmentApi\Models\ParkingCategory;
use Api\Commands\SelectCommand;
use Illuminate\Support\Facades\Validator;

/*
 *	Validates Society Parking. 
 * 
 *	@category	Society Config Import
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 */

class ValidateSocietyParking extends SelectCommand
{
	protected $societyParking;
	
	private $rules = [
		'parking_category'	=> 'required',
		'initial'			=> 'required',
		'slots'				=> 'required|numeric',
		'row'				=> 'required_if:amenties_parking,Multiple Stacked',
		'column'			=> 'required_if:amenties_parking,Multiple Stacked,Single Stacked',
	];
	
	private $exceptions = [
		'required'		=> 'required_:attribute',
		'numeric'		=> 'numeric_:attribute',
		'required_if'	=> 'required_if_:attribute',
		
	];
	
	private $customMessages = [
		'required_parking category'	=> 'Society Parking is empty at row ',
		'required_initial'			=> 'Initial is empty at row ',
		'required_slots'			=> 'Slot is empty at row ',
		'required_if_row'			=> 'Row is empty at row ',
		'required_if_column'		=> 'Column Type is empty at row ',
		'numeric_slots'				=> 'Slot is invalid at row ',
		'numeric_row'				=> 'Row is invalid at row ',
		'numeric_column'			=> 'Column is invalid at row ',
		'invalid_single_slots'	    => 'Slot is not equal with column at row ',
		'invalid_multiple_slots'    => 'Slot is not equal with column at row ',
		'invalid_parking category'	=> 'Society Parking is invalid at row ',
	];
	
	protected $isCustomMessageSet = [
		'required_parking category'	=> false,
		'required_initial'			=> false,
		'required_slots'			=> false,
		'required_if_row'			=> false,
		'required_if_column'		=> false,
		'numeric_slots'				=> false,
		'numeric_row'				=> false,
		'numeric_column'			=> false,
		'invalid_single_slots'		=> false,
		'invalid_multiple_slots'	=> false,
		'invalid_parking category'	=> false,
	];

	protected $sheetName ;
	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($societyParking, $sheet)
	{
		$this->societyParking = $societyParking;
		$this->sheetName = $sheet;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{		
		foreach ($this->societyParking as $index => $societyParking) {
			
			$this->isSlotSame($societyParking, $index);
			
			$validator = Validator::make($societyParking, $this->rules, $this->exceptions);
			if ($validator->fails()) {
				foreach (array_keys($this->rules) as $rule) {
					if ($messageKey = $validator->messages()->first($rule)) {
						$this->isCustomMessageSet[$messageKey] = true;
						$this->customMessages[$messageKey] .= ($index + 2) . ', ';
					}
				}
			}
			else {
				$parkingCategoryId = ParkingCategory::where('category_name', $societyParking['parking_category'])->lists('id');
				if (empty($parkingCategoryId)){
					$this->isCustomMessageSet['invalid_parking category'] = true;
					$this->customMessages['invalid_parking category'] .= ($index + 2) . ', ';
				}
			}
			
			
		}
		if (in_array(true, $this->isCustomMessageSet))
			throw new InvalidSocietyParkingException("Error occured during import.",  $this->getCustomMessages());
	}
	
	protected function isSlotSame($societyParking, $index)
	{
		if (strtolower($societyParking['parking_category']) === 'multiple stacked' and 
			$societyParking['row'] * $societyParking['column'] !== $societyParking['slots']){
				$this->isCustomMessageSet['invalid_multiple_slots'] = true;
				$this->customMessages['invalid_multiple_slots'] = $this->customMessages['invalid_multiple_slots'] . ($index + 2) ;
		}
		
		if (strtolower($societyParking['parking_category']) === 'single stacked' and 
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
