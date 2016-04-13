<?php namespace ApartmentApi\Commands\Building;

use Illuminate\Support\Facades\DB;
use Api\Commands\CreateCommand;
use Illuminate\Support\Facades\Validator;
use ApartmentApi\Models\Block;
use ApartmentApi\Models\BlockConfiguration;
use ApartmentApi\Models\BlockConfigurationFloorInfo;

/*
 *	Creates blockwise amenities. 
 * 
 *	@category	Wings Configuration
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 */

class CreateFlats extends CreateCommand 
{	
	protected $rules = [
        'blockId' => 'required|integer',
		'isFlatSame' => 'required',
		'flats' => 'required',
		'nos_floors' => 'required',
    ];
	
	protected $request;


	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($request)
	{
		$this->request = $request;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(BlockConfiguration $blockConfig)
	{
		if ($this->validationFails(new Validator)) {
            return $this->makeErrorResponse('Block id is required.', 400);
        }
		
		// If Flats are Same on Each Floors :
		if ($this->get('isFlatSame') === 'YES') {
			$blockConfigResult = $blockConfig->fill([
									'block_id'					 => $this->get('blockId'),
									'nos_of_floors'			     => $this->get('nos_floors'),
									'is_flat_same_on_each_floor' => $this->get('isFlatSame'),
									'flat_on_each_floor'         => $this->get('flats'),
								])->save();
			return $blockConfigResult;
		}
		
		// If Flats are not  Same on Each Floors :
		else {
			DB::transaction(function() use ($blockConfig) {
				$blockConfigResult = $blockConfig->fill([
														'block_id'					 => $this->get('blockId'),
														'nos_of_floors'			     => $this->get('nos_floors'),
														'is_flat_same_on_each_floor' => $this->get('isFlatSame'),
														'flat_on_each_floor'         => 0,
													])->save();
			
				for ($i=0 ; $i < count($this->request->get('flats')); $i++ ){
					$blockConfigFloorResult = new BlockConfigurationFloorInfo([
																	'block_configuration_id' => $blockConfig->id,
																	'floor_no'			     => ($i + 1),
																	'no_of_flat'			 => $this->request->get('flats')[$i],
																]);

					$blockConfigFloorResult->save();
				}
				
			});
			return true;
			
		}
	}

}
