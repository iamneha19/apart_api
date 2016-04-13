<?php namespace ApartmentApi\Commands\Import;

use ApartmentApi\Commands\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use ApartmentApi\Exceptions\InvalidWingAmenitiesException;
use Api\Commands\SelectCommand;
use Illuminate\Support\Facades\Validator;
use ApartmentApi\Models\Amenity;


class ValidateWingAmenities extends SelectCommand {

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	protected $wingAmenities;
	
	private $rules = [
	'wing_amenities'	=> 'required',
	];
	
	private $exceptions = [
	'required'	=> 'required_:attribute',
	];
	
	private $customMessages = [
	'required_wing amenities'	=> 'Wing amenities are empty at row ',
	'invalid_wing amenities'		=> 'Wing amenity is invalid at row ',
	];
	
	protected $isCustomMessageSet = [
	'required_wing amenities'	=> false,
	'invalid_wing amenities'		=> false,
	];
	
	protected $sheetName ;
	
	
	
	public function __construct($wingAmenities, $sheet)
	{
		$this->wingAmenities = $wingAmenities;
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
		//
		//dd($this->wingAmenties);
		foreach ($this->wingAmenities as $index => $wingAmenities) {
	       
			
	       
			$validator = Validator::make($wingAmenities, $this->rules, $this->exceptions);
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
				
				$amenityResult = Amenity::where('name', '=', $wingAmenities['wing_amenities'])->first();
			
				if ($amenityResult === null) {
					$this->isCustomMessageSet['invalid_wing amenities'] = true;
					$this->customMessages['invalid_wing amenities'] .= ($index + 2) . ', ';
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
