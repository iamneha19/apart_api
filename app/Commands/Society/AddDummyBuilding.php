<?php

namespace ApartmentApi\Commands\Society;

use ApartmentApi\Commands\Command;
use ApartmentApi\Models\Society;
use ApartmentApi\Models\SocietyConfig;
use Api\Commands\CreateCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use DB;

class AddDummyBuilding extends CreateCommand
{
    protected $rules = [
        'society_id' => 'required'
    ];

    protected $count;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($count, $request)
	{
        $this->count = $count;

        parent::__construct($request);
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(Society $society, SocietyConfig $societyConfig)
	{
        return DB::transaction(function() use ($society, $societyConfig) {
            $society = $society->select('id')
                        ->find($this->get('society_id'));

            $dummyBuildings = $this->createDummyBuildingArray($this->count - $society->buildings->count());

            $society->buildings()->insert($dummyBuildings);

            return $societyConfig
                    ->firstOrCreate([
						'society_id' => $this->get('society_id')
					])
                    ->update([
                        'building_count' => $this->count
                    ]);
        });
	}

    protected function createDummyBuildingArray($remainingBuildingCount)
    {
        $dummyBuildings = [];
        for ($i = 1; $i <= $remainingBuildingCount; $i++) {
            $dummyBuildings[] = [
                'parent_id' => $this->get('society_id'),
                'name' => 'Dummy Building ' . $i
            ];
        }

        return $dummyBuildings;
    }

}
