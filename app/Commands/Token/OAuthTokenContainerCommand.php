<?php namespace ApartmentApi\Commands\Token;

use ApartmentApi\Commands\Command;
use ApartmentApi\Models\OauthToken;
use Api\Commands\SelectCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Http\Request;
use Illuminate\Container\Container;

class OAuthTokenContainerCommand extends Command implements SelfHandling
{
    protected $dispatcher;

    protected $token;

    protected $societyId;

    public function getToken()
    {
        return $this->token ?: $this->loadOAuthToken()->token;
    }

    public function getSocietyId()
    {
        return $this->societyId ?: $this->societyId = $this->getToken()->society_id;
    }

    public function getDispatcher()
    {
        return $this->dispatcher ?: $this->loadDispatcher()->dispatcher;
    }

    public function loadOAuthToken()
    {
        $this->token = $this->getDispatcher()->dispatch(new self);

        return $this;
    }

    public function loadDispatcher()
    {
        $this->dispatcher = app('Illuminate\Bus\Dispatcher');

        return $this;
    }

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(Request $request, OauthToken $oAuthToken)
	{
        return $oAuthToken->find($request->get('access_token'));
	}

}
