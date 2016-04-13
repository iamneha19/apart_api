<?php 

namespace ApartmentApi\Commands\Import;

use ApartmentApi\Exceptions\InvalidBuildingDataException;
use Api\Commands\SelectCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

/*
 *	Validates Society Config data. 
 * 
 *	@category	Society Config Import
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 */

class ValidateBuildings extends SelectCommand
{
	protected $buildings;
	
	private $rules = [
		'building_name'	=> 'required',
		'flat_numbers'	=> 'required|numeric',
		'sq_ft'			=> 'required|numeric',
		'flat_type'		=> 'required|in:Flat,Shop,Office',
		'floor'			=> 'required|numeric',
	];
	
	private $exceptions = [
		'required'	=> 'required_:attribute',
		'numeric'	=> 'numeric_:attribute',
		'in'		=> 'in_:attribute',
		
	];
	
	private $customMessages = [
		'required_floor'				=> 'Floor is empty at row ',
		'required_building name'		=> 'Building Name is empty at row ',
		'required_flat numbers'			=> 'Flat Number is empty at row ',
		'required_sq ft'				=> 'Square Feet is empty at row ',
		'required_flat type'			=> 'Flat Type is empty at row ',
		'numeric_floor'					=> 'Floor is invalid at row ',
		'numeric_flat numbers'			=> 'Flat Number is invalid at row ',
		'numeric_sq ft'					=> 'Square Feet is invalid at row ',
		'in_flat type'					=> 'Flat Type is invalid at row ',
	];
	
	protected $isCustomMessageSet = [
		'required_floor'				=> false,
		'required_building name'		=> false,
		'required_flat numbers'			=> false,
		'required_sq ft'				=> false,
		'required_flat type'			=> false,
		'numeric_floor'					=> false,
		'numeric_flat numbers'			=> false,
		'numeric_sq ft'					=> false,
		'in_flat type'					=> false,
	];

	protected $sheetName ;
	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($buildings, $sheet)
	{
		$this->buildings = $buildings;
		$this->sheetName = $sheet;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{		
//		dd($this->buildings);
		foreach ($this->buildings as $index => $building) {
			$validator = Validator::make($building, $this->rules, $this->exceptions);
			if ($validator->fails()) {
				foreach (array_keys($this->rules) as $rule) {
					if ($messageKey = $validator->messages()->first($rule)) {
						$this->isCustomMessageSet[$messageKey] = true;
						$this->customMessages[$messageKey] .= ($index + 2) . ', ';
					}
				}
			}
		}
		if (in_array(true, $this->isCustomMessageSet))
			throw new InvalidBuildingDataException("Error occured during import.",  $this->getCustomMessages());
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
