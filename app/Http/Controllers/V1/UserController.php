<?php namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Http\Requests\User\CreateRequest as UserCreateRequest;
use ApartmentApi\Http\Middleware\CheckOAuth;
use ApartmentApi\Commands\User\CreateCommand as UserCreateCommand;
use ApartmentApi\Commands\User\SearchUserCommand;
use ApartmentApi\Models\User;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserController extends ApiController
{

    public function index(Request $request, SearchUserCommand $search)
    {
        $users = $this->dispatch($search);

        // If first is present then that means user exist.
        if($users->first()) {
            return $this->presentor()->make200Response('Successfully loaded.', $users->toArray());
        }

        return $this->presentor()->make404Response('Not found.');
    }

    public function create(UserCreateRequest $request, UserCreateCommand $createCommand)
    {
        return $this->dispatch($createCommand);
    }

    public function status(Request $request, User $user)
    {
        if ($request->has('id'))
        {
            if ($user = $user->select('active_status')->find($request->get('id')))
            {
                return ($user->active_status === 1) ?
                    $this->presentor->make200Response('User is active'):
                    $this->presentor->make422Response('User is disable');
            }

            return $this->presentor->make404Response('User not found.');
        }

        return $this->presentor->make400Response('User id is required');
    }

}
