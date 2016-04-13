<?php

namespace ApartmentApi\Commands\Society;

use Api\Commands\CreateCommand;
use Illuminate\Http\Request;
use ApartmentApi\Models\Society;

/**
 * Add building first Configuration layer to DB
 *
 * @author Mohammed Mudasir
 */
class SaveBuildingsConfig extends CreateCommand
{
    protected $rules = [
        'society_id' => '',
        'building_amenities' => '',
        'building_names' => '',
        'building_has_wing' => '',
        'building_names' => '',
    ];

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(Society $society)
	{
        for ($i = 0; $i < $this->get('building_count'); $i++) {
            $building = $society->firstOrNewBuilding($this->get('society_id'), ['id' => $this->get('building_ids')[$i]
            ])
            ->fill([
                'name' => $this->get('building_names')[$i],
                'wing_exists' => $this->get('building_has_wing')[$i]
            ]);

            $building->save();

            $amenities = [];

            if (isset($this->get('building_amenities')[$i])) {
                foreach ($this->get('building_amenities')[$i] as $amenityId) {
                    $amenities[$amenityId] = [
                        'society_id' => $this->get('society_id')
                    ];
                }
            }

            $building->amenities()->sync($amenities);
    	}

        return true;
    }

}
