<?php 

namespace ApartmentApi\Commands\Import;

use ApartmentApi\Exceptions\InvalidSocietyAmenitiesException;
use ApartmentApi\Models\Amenity;
use Api\Commands\SelectCommand;
use Illuminate\Support\Facades\Validator;

/*
 *	Validates Society Amenities. 
 * 
 *	@category	Society Config Import
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 */

class ValidateSocietyAmenites extends SelectCommand
{
	protected $societyAmenities;
	
	private $rules = [
		'society_amenities'	=> 'required',
	];
	
	private $exceptions = [
		'required'	=> 'required_:attribute',
	];
	
	private $customMessages = [
		'required_society amenities'	=> 'Society Amenities are empty at row ',
		'invalid_society amenities'		=> 'Society Amenity is invalid at row ',
	];
	
	protected $isCustomMessageSet = [
		'required_society amenities'	=> false,
		'invalid_society amenities'		=> false,
	];

	protected $sheetName ;
	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($societyAmenities, $sheet)
	{
		$this->societyAmenities = $societyAmenities;
		$this->sheetName = $sheet;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{		
		foreach ($this->societyAmenities as $index => $societyAmenities) {
			$validator = Validator::make($societyAmenities, $this->rules, $this->exceptions);
			if ($validator->fails()) {
				foreach (array_keys($this->rules) as $rule) {
					if ($messageKey = $validator->messages()->first()) {
						$this->isCustomMessageSet[$messageKey] = true;
						$this->customMessages[$messageKey] .= ($index + 2) . ', ';
					}
				}
			} else {
				$amenityResult = Amenity::where('name', '=', $societyAmenities['society_amenities'])->first();
				
				if ($amenityResult === null) {
					$this->isCustomMessageSet['invalid_society amenities'] = true;
					$this->customMessages['invalid_society amenities'] .= ($index + 2) . ', ';
				}
			}
		}
		if (in_array(true, $this->isCustomMessageSet))
			throw new InvalidSocietyAmenitiesException("Error occured during import.",  $this->getCustomMessages());
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
