<?php namespace ApartmentApi\Commands\BuildingConfig;

use ApartmentApi\Repositories\BuildinConfigRepository;
use Api\Commands\SelectCommand;

class SaveBuildingConfig extends SelectCommand {

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
        protected $rules = [
        'no_of_floor'  => 'required',
        'is_flat_same_on_each_floor' => 'required',
        'flat_on_each_floor'    => 'required',
        
        ];

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(BuildinConfigRepository $repo)
	{          
            return $repo->storeBuilding($this->request);
	}

}
