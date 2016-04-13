<?php namespace ApartmentApi\Commands\Building;

use Api\Commands\SelectCommand;
use Illuminate\Contracts\Bus\SelfHandling;
use ApartmentApi\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/*
 *	Checks and returns block with amenities. 
 * 
 *	@category	Wings Configuration
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 * 
 */

class FindBlock extends SelectCommand {
	
	protected $request;
	
	protected $rules = [
        'blockId' => 'required',
    ];
	
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
		if ($this->validationFails(new Validator)) {
            return $this->makeErrorResponse('Block id is required.', 400);
        }
		
		return $block->whereId($this->request->get('blockId'))
				     ->with([
							'amenities' => function($q) {
								$q->select( 'amenity_id');
							}])
					->paginate();
	}

}
