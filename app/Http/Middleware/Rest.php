<?php
namespace ApartmentApi\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\ServerBag;
use Illuminate\Http\Request;
use LucaDegasperi\OAuth2Server\Authorizer;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\User;

class Rest {

	protected $user;

	public function __construct(User $user) {
		$this->user = $user;
	}

	public function handle(Request $request, Closure $next) {

		$token = OauthToken::find($request->get('access_token'),['token']);

		if (!$token)
			abort(403,'Invalid Accesstoken');

// 		if (!$request->route()->getName()) {
// 			abort(500,'No route name defined');
// 		}

/* 		$split = explode('.', $request->route()->getName());

		if ($this->user->hasPermission($split[0].'.'.$split[1], $split[2],$token->token)){

			$response = $next($request);

			return response()->json([
				'status_code'=>$request->server->get('REDIRECT_STATUS'),
				'response'=>$response
			]);

		} else {

			abort(403,'Unauthorized access');
		}
 */
		$response = $next($request);

        if ( is_array($response) and isset($response['useTotal'])) {
            return response()->json([
				'status_code'=>$request->server->get('REDIRECT_STATUS'),
                'total' => $response['useTotal'],
				'response'=>$response['results'],
            ]);
        }

		return response()->json([
				'status_code'=>$request->server->get('REDIRECT_STATUS'),
				'response'=>$response
		]);

	}

	private function checkPermission($route,$accessToken,User $user) {

		return $user->hasPermission($route, $accessToken);

	}

}
