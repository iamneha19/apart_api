<?php namespace ApartmentApi\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',
		//'ApartmentApi\Http\Middleware\VerifyCsrfToken',
		];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'auth' => 'ApartmentApi\Http\Middleware\Authenticate',
		'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
		'guest' => 'ApartmentApi\Http\Middleware\RedirectIfAuthenticated',
		//'csrf' => 'ApartmentApi\Http\Middleware\VerifyCsrfToken',
		'rest' => 'ApartmentApi\Http\Middleware\Rest',
        'super_admin_rest' => 'ApartmentApi\Http\Middleware\SuperAdminRest',
        'acl_middleware' => 'ApartmentApi\Http\Middleware\AclMiddleware'
	];

}
