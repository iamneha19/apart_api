<?php namespace ApartmentApi\Http\Requests\User;

use ApartmentApi\Http\Requests\Request;
use Api\Traits\ApiResponseTrait;

class CreateRequest extends Request
{
    use ApiResponseTrait;
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
            'society_id' => 'required:interger',
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required',
			'contact_no' => 'required:integer',
		];
	}

    public function response(array $error)
    {
        return response()->make($this->make400Response('Validation failed.', $error));
    }

}
