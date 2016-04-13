<?php namespace ApartmentApi\Commands\Building;

use Api\Commands\SelectCommand;
use Illuminate\Contracts\Bus\SelfHandling;
use ApartmentApi\Models\Block;
use ApartmentApi\Models\BlockConfiguration;
use ApartmentApi\Models\BlockConfigurationFloorInfo;
use Illuminate\Support\Facades\Validator;


/*
 *	Checks and returns blockwise flats. 
 * 
 *	@category	Wings Configuration
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 * 
 */

class ListFlats extends SelectCommand  {

	protected $rules = [
		'blockId' => 'required|integer',
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

		$blockConfigResult = BlockConfiguration::with(['block' => function($q) {
													 }])->whereBlockId($this->request->get('blockId'))->first();

		if ( $blockConfigResult){
			if ($blockConfigResult->toArray()['is_flat_same_on_each_floor'] === 'NO'){
				$blockConfigResult = BlockConfiguration::with(['blockConfig' => function($q) {
																	$q->select('block_configuration_id','floor_no','no_of_flat');
																}])
														->with(['block' => function($q) {
																	$q->select('id','block');
															   }])
														->whereBlockId($this->request->get('blockId'))
														->first();
			}
		}
		else {
			$blockConfigResult = Block::whereId($this->request->get('blockId'))->first();
		}
		
		return $blockConfigResult->toArray();
	}
}
