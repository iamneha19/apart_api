<?php namespace ApartmentApi\Commands\Building;

use Illuminate\Support\Facades\DB;
use Api\Commands\UpdateCommand;
use Illuminate\Support\Facades\Validator;
use ApartmentApi\Models\Block;
use ApartmentApi\Models\BlockConfiguration;
use ApartmentApi\Models\BlockConfigurationFloorInfo;

/*
 *	Checks and updates blockwise flats. 
 * 
 *	@category	Wings Configuration
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 * 
 */

class UpdateFlats extends UpdateCommand {

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
		$blockConfigResult = $blockConfig->firstOrNew([
								'block_id'    => $this->get('blockId')
							]);

		$updateArray = array (	'block_id'                   => $this->get('blockId'),
								'nos_of_floors'			     => $this->get('nos_floors'),
								'is_flat_same_on_each_floor' => $this->get('isFlatSame'),
							 );

        if (! $blockConfigResult->block_id) {
        	return $this->makeErrorResponse('Block Configuration  does not exists.', 404);
        } else {
					if ($this->get('isFlatSame') === 'YES') {
						$updateArray = array_merge($updateArray, array('flat_on_each_floor' => $this->get('flats')));
						return $this->withSameFlats($blockConfig,$updateArray);
						
						
					} else if ($this->get('isFlatSame') === 'NO') {
						$updateArray = array_merge($updateArray, array('flat_on_each_floor' => 0));
						return $this->withDiffFlats($blockConfig,$updateArray);
					}
				}
	}
	
	public function withSameFlats($blockConfig, $updateArray)
	{
		$blockConfigUpdate = $blockConfig->where('block_id',$this->get('blockId'))->first();
		$blockConfigUpdateResult =  $blockConfigUpdate->update($updateArray);
						
		$blockConfigFloorDelete = BlockConfigurationFloorInfo::where('block_configuration_id',$blockConfigUpdate->id)->delete();
		
		return true;
	}
	
	public function withDiffFlats($blockConfig, $updateArray)
	{
		DB::transaction(function() use ($blockConfig, $updateArray) {
			$blockConfigUpdate = $blockConfig->where('block_id',$this->get('blockId'))->first();
			$blockConfigUpdateResult =  $blockConfigUpdate->update($updateArray);
						
			$blockConfigFloorDelete = BlockConfigurationFloorInfo::where('block_configuration_id',$blockConfigUpdate->id)->delete();

			for ($i=0 ; $i < count($this->request->get('flats')); $i++ ){
				$blockConfigFloorResult = new BlockConfigurationFloorInfo([
																			'block_configuration_id' => $blockConfigUpdate->id,
																			'floor_no'			     => ($i + 1),
																			'no_of_flat'			 => $this->request->get('flats')[$i],
																		  ]);
				$blockConfigFloorResult->save();
			}
		});
		
		return true;
	}

}
