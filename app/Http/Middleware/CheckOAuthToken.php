<?php namespace ApartmentApi\Http\Middleware;

use Closure;
use Illuminate\Bus\Dispatcher;
use Api\Traits\ApiResponseTrait;
use ApartmentApi\Http\Middleware\ValidSuperAdminOAuth;
use ApartmentApi\Http\Middleware\ValidOAuth;

/**
 * Determine token belongs to super admin or admin
 * 
 * @author Mohammed Mudasir
 */
class CheckOAuthToken 
{
    use ApiResponseTrait;
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $validSuperAdminOAuth = new ValidSuperAdminOAuth;
        $validOAuth = new ValidOAuth;
            
        $response = $validSuperAdminOAuth->handle($request, $next);
        
        if (is_array($response) and $response['status'] == 'validation_failed') {
            
            // Check if token belongs to super admin or not
            if ($response['message'] == 'Invalid access token provided.') {
                $response = $validOAuth->handle($request, $next);
            }
        }
        
        return $response;
    }

}
