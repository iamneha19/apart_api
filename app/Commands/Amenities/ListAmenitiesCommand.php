<?php namespace ApartmentApi\Commands\Amenities;

use ApartmentApi\Repositories\AmenityRepository;
use Api\Commands\SelectCommand;
use Illuminate\Contracts\Bus\SelfHandling;

/*
 *	Checks and returns amenities. 
 * 
 *	@category	Wings Configuration
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 * 
 */

class ListAmenitiesCommand extends SelectCommand
{
	protected $societyId;

    protected $rules = [
        'paginate' => ''
    ];

    protected $message = 'Successfully loaded.';

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(AmenityRepository $repo)
	{
        $repo = $repo->fewSelection()
				     ->orderBy('id', 'DESC');

        if ($this->get('paginate') == 'no') {
            return $repo->select('id', 'name as text')->get();
        }

		return $repo->paginate();
	}

}
