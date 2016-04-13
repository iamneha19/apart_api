<?php

namespace ApartmentApi\Http\Requests;

use ApartmentApi\Http\Requests\Request;
use Api\Traits\ApiResponseTrait;

class BillingConfigRequest extends Request
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
            'office_charge'     => 'required|integer|min:1',
            'shop_charge'       => 'required|integer|min:1',
            'residential_charge'=> 'required|integer|min:1',
            'interest_rate'     => 'required|integer|min:1|max:99',
            'service_tax'       => 'required|integer|min:1|max:99',
		];
	}

    public function response(array $error)
    {
        return response()->make($this->make400Response('Please fill all required fields.', $error));
    }

}
