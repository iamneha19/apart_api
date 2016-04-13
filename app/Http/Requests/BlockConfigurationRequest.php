<?php namespace ApartmentApi\Http\Requests;

use ApartmentApi\Http\Requests\Request;
use Api\Traits\ApiResponseTrait;

class BlockConfigurationRequest extends Request
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
			'blockId' => 'required|integer',
			'isFlatSame' => 'required',
			'flats' => 'required',
			'nos_floors' => 'required',
		];
	}

    public function response(array $error)
    {
        return response()->make($this->make400Response('Block Id/IsFlat Same/Flats a required or invalid.', $error));
    }

}
