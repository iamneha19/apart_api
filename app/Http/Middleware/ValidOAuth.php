<?php namespace ApartmentApi\Http\Middleware;

use Closure;
use ApartmentApi\Repositories\OAuthRepository;
use ApartmentApi\Models\OauthToken;
use Illuminate\Http\Request;
use Illuminate\Container\Container;
use Api\Traits\ApiResponseTrait;
use Repository\Contracts\AccessTokenContract;
use Illuminate\Contracts\Bus\SelfHandling;

class ValidOAuth implements SelfHandling
{
    use ApiResponseTrait;

    public $oAuthRepo;

    public function __construct()
    {
        $this->oAuthRepo = new OAuthRepository(new OauthToken);
    }

	public function handle(Request $request, Closure $next)
    {
        if (! $request->has('access_token'))
        {
            return $this->make400Response('Access token is required.');
        }

        if (! $this->oAuthRepo->isAccessTokenValid($request->get('access_token')))
        {
            return $this->make400Response('Invalid access token provided.');
        }

        return $next($request);
	}

}
