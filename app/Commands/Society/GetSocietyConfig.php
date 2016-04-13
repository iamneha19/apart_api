<?php

namespace ApartmentApi\Commands\Society;

use Api\Commands\SelectCommand;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Http\Request;
use ApartmentApi\Repositories\SocietyConfigRepository;

class GetSocietyConfig extends SelectCommand
{
    protected $rules = [
        'society_id' => 'required'
    ];

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(SocietyConfigRepository $repo)
	{
        $config = $repo->find($this->get('society_id'));

        return $this->make200Response('Successfully loaded.', $config);
	}

}
