<?php namespace ApartmentApi\Commands\Import;

use ApartmentApi\Commands\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use ApartmentApi\Exceptions\InvalidWingAmenitiesException;
use Api\Commands\SelectCommand;
use Illuminate\Support\Facades\Validator;
use ApartmentApi\Models\Amenity;


class ValidateBuildingAmenities extends SelectCommand {

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	protected $buildingAmenities;
	
	private $rules = [
	'building_amenities'	=> 'required',
	];
	
	private $exceptions = [
	'required'	=> 'required_:attribute',
	];
	
	private $customMessages = [
	'required_building amenities'	=> 'building amenities are empty at row ',
	'invalid_building amenities'		=> 'building amenity is invalid at row ',
	];
	
	protected $isCustomMessageSet = [
	'required_building amenities'	=> false,
	'invalid_building amenities'	=> false,
	];
	
	protected $sheetName ;
	
	
	
	public function __construct($buildingAmenities, $sheet)
	{
		$this->buildingAmenities = $buildingAmenities;
		$this->sheetName = $sheet;
		//
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		foreach ($this->buildingAmenities as $index => $buildingAmenities) {
			$validator = Validator::make($buildingAmenities, $this->rules, $this->exceptions);
			if ($validator->fails()) 
			{	
				foreach (array_keys($this->rules) as $rule) 
				{
					if ($messageKey = $validator->messages()->first()) {
						$this->isCustomMessageSet[$messageKey] = true;
						$this->customMessages[$messageKey] .= ($index + 2) . ', ';
					}
				}
				
			}
			else{
				
				$amenityResult = Amenity::where('name', '=', $buildingAmenities['building_amenities'])->first();
			
				if ($amenityResult === null) {
					$this->isCustomMessageSet['invalid_building amenities'] = true;
					$this->customMessages['invalid_building amenities'] .= ($index + 2) . ', ';
				}
			}
			
			if (in_array(true, $this->isCustomMessageSet))
				throw new InvalidWingAmenitiesException("Error occured during import.",  $this->getCustomMessages());
		
		
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
