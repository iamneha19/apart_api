<?php

namespace ApartmentApi\Commands\Society;

use ApartmentApi\Commands\Command;
use Api\Commands\CreateCommand;
use ApartmentApi\Models\Amenity;
use ApartmentApi\Models\SocietyConfig;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Bus\Dispatcher;
use DB;

class SaveSocietyConfig extends CreateCommand
{
    protected $rules = [
        'amenities' => '',
        'building_count' => '',
        'building_ids' => '',
        'building_amenities' => '',
        'building_has_wing' => '',
        'building_names' => '',
        'society_id' => ''
    ];

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(SocietyConfig $societyConfig, Dispatcher $dispatcher)
	{
        DB::transaction(function() use ($societyConfig, $dispatcher) {
    		$this->societyConfig = $societyConfig;

            $this->saveConfig();

            $dispatcher->dispatch(SaveBuildingsConfig::instance($this->request));
        });

        return $this->make200Response('Successfully Configured.');
	}

    public function saveConfig()
    {
        $societyConfig = $this->societyConfig
                            ->firstOrCreate(['society_id' => $this->get('society_id')]);

        $updated = $societyConfig->update([
            'building_count' => $this->get('building_count')
        ]);
        $amenities = [];

        foreach ($this->get('amenities') as $amenity) {
            $amenities[$amenity] = [
                'society_id' => $this->get('society_id')
            ];
        }

        return $societyConfig
                    ->amenities()
                    ->sync($amenities);
    }

}
