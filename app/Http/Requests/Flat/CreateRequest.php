<?php namespace ApartmentApi\Http\Requests\Flat;

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
			'building_id'  => 'required',
            'block_id'     => 'required',
            'flat_no'   => 'required',
            'type'      => 'required',
            'relation'  => 'required',
		];
	}

    public function response(array $error)
    {
        return response()->make($this->make400Response('Validation failed.', $error));
    }
}
