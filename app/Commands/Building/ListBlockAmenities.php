<?php namespace ApartmentApi\Commands\Building;

use ApartmentApi\Models\Block;
use Api\Commands\SelectCommand;

/*
 *	Checks and returns blockwise amenities. 
 * 
 *	@category	Wings Configuration
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 * 
 */

class ListBlockAmenities extends SelectCommand {

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
	public function handle(Block $block)
	{
		$result = $block->whereId($this->request->get('blockId'))->first();
		
		return $result->amenities()->get();
	}

}
