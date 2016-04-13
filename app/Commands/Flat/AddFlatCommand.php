<?php namespace ApartmentApi\Commands\Flat;

use ApartmentApi\Commands\Command;
use ApartmentApi\Repositories\FlatRepository;
use Api\Commands\CreateCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddFlatCommand extends CreateCommand
{
    protected $rules = [
        'society_id'  => 'required',
        'building_id' => 'required',
        'block_id'    => '',
        'square_feet_1' => 'required',
        'flat_no'     => 'required',
        'floor' => 'required',
        'type'        => 'required',
        'relation'    => 'required',
    ];
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Request $request)
	{
        parent::__construct($request);
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(Validator $validator, FlatRepository $repo)
	{
		if ($this->validationFails($validator)) {
            return $this->make400Response('Validation failed.', $this->getError());
        }

        return $repo->add($this->fields) ?
                    $this->make200Response($this->getMessage()):
                    $this->make400Response('Flat already exists.');
	}

}
