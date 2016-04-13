<?php

namespace ApartmentApi\Http\Middleware;

use Closure;
use ApartmentApi\Repositories\OAuthRepository;
use ApartmentApi\Models\OauthSuperAdminToken;
use Illuminate\Http\Request;
use Api\Traits\ApiResponseTrait;
use Repository\Contracts\AccessTokenContract;
use Illuminate\Contracts\Bus\SelfHandling;

class ValidSuperAdminOAuth implements SelfHandling
{
    use ApiResponseTrait;

    public $oAuthRepo;

    public function __construct()
    {
        $this->oAuthRepo = new OAuthRepository(new OauthSuperAdminToken);
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
