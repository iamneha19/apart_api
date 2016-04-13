<?php

namespace Api\Middleware;

use Api\Traits\ApiResponseTrait;

/**
 * Api middleware
 *
 * @author Mohammed Mudasir
 */
class ApiResponseMiddleware
{
    use ApiResponseTrait;

    function __construct()
    {
        # code...
    }

    public function handle(Request $request, Closure $closure)
    {
        $response = next($closure);
    }
}
