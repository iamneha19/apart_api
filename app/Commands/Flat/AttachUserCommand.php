<?php namespace ApartmentApi\Commands\Flat;

use ApartmentApi\Commands\Command;
use ApartmentApi\Repositories\FlatRepository;
use ApartmentApi\Models\Flat;
use ApartmentApi\Models\UserSociety;
use Api\Commands\SelectCommand;
use DB;

class AttachUserCommand extends SelectCommand
{
    protected $rules = [
        'society_id' => 'required',
        'flat_id' => 'required',
        'user_id' => 'required',
    ];

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(UserSociety $userSociety, Flat $flat)
	{
        $user = $userSociety->where($this->only('society_id', 'flat_id'))->first();

        if ($user->user_id) {
            return $this->make400Response('User is already attached to a flat.');
        }

        if ($user->update(array_merge($this->only('user_id'), ['status' => 1]))) {
            $user->where([
                    'user_id' => $this->get('user_id'),
                    'building_id' => null
                ])
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->delete();

            return $this->make200Response('Successfully attached.');
        }

        return $this->make500Response('Unable to attach user, please try again later.');
	}
}
