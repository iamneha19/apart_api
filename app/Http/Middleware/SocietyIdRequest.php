<?php namespace ApartmentApi\Http\Middleware;

use Api\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class SocietyIdRequest extends FormRequest
{
    use ApiResponseTrait;

    public function rules()
    {
        return [
            'society_id' => 'required|integer'
        ];
    }

    public function authorize()
    {
        return true;
    }

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		return $next($request);
	}

    public function response(array $errors)
    {
        return $this->make400Response('Validation Failed', $errors);
    }
}
