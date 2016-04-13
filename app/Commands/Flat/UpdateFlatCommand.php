<?php namespace ApartmentApi\Commands\Flat;

use ApartmentApi\Commands\Command;
use ApartmentApi\Repositories\FlatRepository;
use ApartmentApi\Models\Flat;
use Api\Commands\UpdateCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Database\QueryException;

class UpdateFlatCommand extends UpdateCommand
{
    protected $rules = [
        'building_id' => 'required',
        'block_id'    => '',
        'flat_no'     => 'required',
        'square_feet_1' => 'required',
        'type'        => 'required',
        'relation'    => 'required',
    ];

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(FlatRepository $repo, Flat $flat)
	{
            
        $blockId = $this->get('block_id');
        $flatNo  = $this->get('flat_no');
        $alreadyExists = $flat->where([
            'block_id' => $blockId,
            'flat_no'  => $flatNo
        ])->first();

        if ($alreadyExists and $alreadyExists->id != $this->get('id')) {
          
            return $this->make400Response('Flat already exists.');
        }

        $repo->edit($this->get('id'), $this->fields);

        return $this->make200Response($this->getMessage());
	}

}
