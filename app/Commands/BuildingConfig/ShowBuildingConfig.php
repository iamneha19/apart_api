<?php namespace ApartmentApi\Commands\BuildingConfig;

use ApartmentApi\Repositories\BuildinConfigRepository;
use Api\Commands\SelectCommand;

class ShowBuildingConfig extends SelectCommand {

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	protected $rules = [
        'building_id'  => 'required',
        ];

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(BuildinConfigRepository $repo)
	{
		return $repo->showBuildingConfig($this->request);
	}

}
