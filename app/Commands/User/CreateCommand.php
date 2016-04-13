<?php namespace ApartmentApi\Commands\User;

use ApartmentApi\Commands\Command;
use Api\Commands\SelectCommand;
use ApartmentApi\Http\Requests\User\CreateRequest;
use ApartmentApi\Repositories\UserRepository;
use DB;
use ApartmentApi\Events\UserWasCreated;
use ApartmentApi\Models\Society;

class CreateCommand extends SelectCommand
{
    protected $rules = [
        'society_id' => 'required',
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required',
        'contact_no' => 'required',
    ];

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(CreateRequest $request)
	{
        parent::__construct($request);
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(UserRepository $userRepo, Society $society)
	{
        $attributes = $this->request->only('first_name', 'last_name', 'email', 'contact_no');
        $password = str_random(8);
        $attributes['password'] = bcrypt($password);

        if ($userRepo->setFields($attributes)->isUserAdmin($attributes)) {
            return $this->make400Response('User is already registered as admin.');
        }

        if ($userRepo->add($this->get('society_id'))) {
            // Overwriting encrypted password to send actual password to user.
            $attributes['password'] = $password;
            $attributes['society_name'] = $society->find($this->get('society_id'))->name;
            $attributes['name'] = ucwords($attributes['first_name'] . ' ' . $attributes['last_name']);

            event(new UserWasCreated($attributes));

            return $this->make200Response('User registered successfully.');
        }

        return $this->make500Response('User already registered in this society or admin of another society.');
	}

}
